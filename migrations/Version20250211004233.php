<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250211004233 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE competition_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE sport_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE competition (id INT NOT NULL, sport_id INT NOT NULL, name VARCHAR(100) NOT NULL, url VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B50A2CB1AC78BCF8 ON competition (sport_id)');
        $this->addSql('CREATE TABLE sport (id INT NOT NULL, name VARCHAR(50) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE competition ADD CONSTRAINT FK_B50A2CB1AC78BCF8 FOREIGN KEY (sport_id) REFERENCES sport (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event ADD competition_id INT NOT NULL');
        $this->addSql('ALTER TABLE event DROP sport');
        $this->addSql('ALTER TABLE event DROP competition');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA77B39D312 FOREIGN KEY (competition_id) REFERENCES competition (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_3BAE0AA77B39D312 ON event (competition_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE event DROP CONSTRAINT FK_3BAE0AA77B39D312');
        $this->addSql('DROP SEQUENCE competition_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE sport_id_seq CASCADE');
        $this->addSql('ALTER TABLE competition DROP CONSTRAINT FK_B50A2CB1AC78BCF8');
        $this->addSql('DROP TABLE competition');
        $this->addSql('DROP TABLE sport');
        $this->addSql('DROP INDEX IDX_3BAE0AA77B39D312');
        $this->addSql('ALTER TABLE event ADD sport VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE event ADD competition VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE event DROP competition_id');
    }
}
