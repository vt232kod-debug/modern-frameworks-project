<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260918120342 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE customers (id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, email VARCHAR(180) NOT NULL, phone VARCHAR(20) DEFAULT NULL, birth_date DATE DEFAULT NULL, UNIQUE INDEX UNIQ_62534E21E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE halls (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, type VARCHAR(10) NOT NULL, rows_count INT NOT NULL, seats_per_row INT NOT NULL, UNIQUE INDEX UNIQ_AD736D9E5E237E06 (name), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE movies (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, genre VARCHAR(100) NOT NULL, duration_minutes INT NOT NULL, release_date DATE DEFAULT NULL, age_rating VARCHAR(10) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE screenings (id INT AUTO_INCREMENT NOT NULL, starts_at DATETIME NOT NULL, price NUMERIC(8, 2) NOT NULL, language VARCHAR(20) NOT NULL, movie_id INT NOT NULL, hall_id INT NOT NULL, INDEX IDX_350DCAA38F93B6FC (movie_id), INDEX IDX_350DCAA352AFCFD6 (hall_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tickets (id INT AUTO_INCREMENT NOT NULL, seat_row INT NOT NULL, seat_number INT NOT NULL, price NUMERIC(8, 2) NOT NULL, status VARCHAR(20) NOT NULL, purchased_at DATETIME NOT NULL, screening_id INT NOT NULL, customer_id INT NOT NULL, UNIQUE INDEX uniq_ticket_seat (screening_id, seat_row, seat_number), INDEX IDX_54469DF470F5295D (screening_id), INDEX IDX_54469DF49395C3F3 (customer_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE screenings ADD CONSTRAINT FK_350DCAA38F93B6FC FOREIGN KEY (movie_id) REFERENCES movies (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE screenings ADD CONSTRAINT FK_350DCAA352AFCFD6 FOREIGN KEY (hall_id) REFERENCES halls (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tickets ADD CONSTRAINT FK_54469DF470F5295D FOREIGN KEY (screening_id) REFERENCES screenings (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tickets ADD CONSTRAINT FK_54469DF49395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE screenings DROP FOREIGN KEY FK_350DCAA38F93B6FC');
        $this->addSql('ALTER TABLE screenings DROP FOREIGN KEY FK_350DCAA352AFCFD6');
        $this->addSql('ALTER TABLE tickets DROP FOREIGN KEY FK_54469DF470F5295D');
        $this->addSql('ALTER TABLE tickets DROP FOREIGN KEY FK_54469DF49395C3F3');
        $this->addSql('DROP TABLE customers');
        $this->addSql('DROP TABLE halls');
        $this->addSql('DROP TABLE movies');
        $this->addSql('DROP TABLE screenings');
        $this->addSql('DROP TABLE tickets');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
