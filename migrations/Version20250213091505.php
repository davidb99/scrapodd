<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250213091505 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bookmaker ADD csv_id INT NOT NULL');
        $this->addSql('ALTER TABLE competition ADD slug VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE competition ADD csv_id INT NOT NULL');
        $this->addSql('ALTER TABLE sport ADD csv_id INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE competition DROP slug');
        $this->addSql('ALTER TABLE competition DROP csv_id');
        $this->addSql('ALTER TABLE sport DROP csv_id');
        $this->addSql('ALTER TABLE bookmaker DROP csv_id');
    }
}
