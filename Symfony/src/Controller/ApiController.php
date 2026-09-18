<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Exception\JsonException;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Shared JSON CRUD helpers for the API controllers.
 */
abstract class ApiController extends AbstractController
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly SerializerInterface $serializer,
        protected readonly ValidatorInterface $validator,
    ) {
    }

    /**
     * Fills $entity from the JSON body, resolves relations, validates and saves it.
     *
     * @param array<string, array{class-string, callable(?object): mixed}> $references
     *        request key => [related entity class, setter], e.g. 'movieId' => [Movie::class, $screening->setMovie(...)]
     * @param (callable(): void)|null $beforeValidate hook for derived values (defaults etc.)
     */
    protected function saveEntity(
        Request $request,
        object $entity,
        string $group,
        int $status,
        array $references = [],
        ?callable $beforeValidate = null,
    ): JsonResponse {
        try {
            $data = $request->toArray();
            $this->serializer->deserialize($request->getContent(), $entity::class, 'json', [
                AbstractNormalizer::OBJECT_TO_POPULATE => $entity,
                AbstractNormalizer::GROUPS => [$group.':write'],
            ]);
        } catch (JsonException|SerializerException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $errors = $this->bindReferences($data, $references);
        if ($beforeValidate !== null) {
            $beforeValidate();
        }

        foreach ($this->validator->validate($entity) as $violation) {
            $errors[$violation->getPropertyPath()][] = $violation->getMessage();
        }
        if ($errors) {
            return $this->json(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->em->persist($entity);
        $this->em->flush();

        return $this->json($entity, $status, context: ['groups' => $group.':read']);
    }

    protected function deleteEntity(object $entity): JsonResponse
    {
        $this->em->remove($entity);
        $this->em->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @return array<string, list<string>> errors keyed by request field
     */
    private function bindReferences(array $data, array $references): array
    {
        $errors = [];
        foreach ($references as $key => [$class, $setter]) {
            if (!array_key_exists($key, $data)) {
                continue;
            }
            $id = $data[$key];
            if ($id === null) {
                $setter(null);
                continue;
            }
            $related = is_int($id) || (is_string($id) && ctype_digit($id)) ? $this->em->find($class, (int) $id) : null;
            if ($related === null) {
                $errors[$key][] = sprintf('%s with id "%s" not found.', (new \ReflectionClass($class))->getShortName(), is_scalar($id) ? $id : gettype($id));
                continue;
            }
            $setter($related);
        }

        return $errors;
    }
}
