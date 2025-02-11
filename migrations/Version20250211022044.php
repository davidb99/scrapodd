<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250211022044 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE bookmaker_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE competition_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE competition_bookmaker_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE event_bookmaker_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE odd_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE odds_snapshot_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE sport_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE bookmaker (id INT NOT NULL, name VARCHAR(100) NOT NULL, slug VARCHAR(50) NOT NULL, website VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE competition (id INT NOT NULL, sport_id INT NOT NULL, name VARCHAR(100) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B50A2CB1AC78BCF8 ON competition (sport_id)');
        $this->addSql('CREATE TABLE competition_bookmaker (id INT NOT NULL, competition_id INT NOT NULL, bookmaker_id INT NOT NULL, url VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_294108C57B39D312 ON competition_bookmaker (competition_id)');
        $this->addSql('CREATE INDEX IDX_294108C58FB29728 ON competition_bookmaker (bookmaker_id)');
        $this->addSql('COMMENT ON COLUMN competition_bookmaker.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN competition_bookmaker.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE event_bookmaker (id INT NOT NULL, competition_bookmaker_id INT NOT NULL, bookmaker_event_id VARCHAR(50) NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, participant1 VARCHAR(100) NOT NULL, participant2 VARCHAR(100) NOT NULL, status VARCHAR(20) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_4347A9A38276ABC ON event_bookmaker (competition_bookmaker_id)');
        $this->addSql('COMMENT ON COLUMN event_bookmaker.date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN event_bookmaker.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN event_bookmaker.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE odd (id INT NOT NULL, odds_snapshot_id INT NOT NULL, outcome VARCHAR(100) NOT NULL, value NUMERIC(6, 2) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F7845EED5F4CDCDF ON odd (odds_snapshot_id)');
        $this->addSql('COMMENT ON COLUMN odd.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE odds_snapshot (id INT NOT NULL, event_bookmaker_id INT NOT NULL, market VARCHAR(50) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_1454814462A19DDB ON odds_snapshot (event_bookmaker_id)');
        $this->addSql('COMMENT ON COLUMN odds_snapshot.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE sport (id INT NOT NULL, name VARCHAR(50) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('COMMENT ON COLUMN messenger_messages.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.available_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.delivered_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
            BEGIN
                PERFORM pg_notify(\'messenger_messages\', NEW.queue_name::text);
                RETURN NEW;
            END;
        $$ LANGUAGE plpgsql;');
        $this->addSql('DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;');
        $this->addSql('CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();');
        $this->addSql('ALTER TABLE competition ADD CONSTRAINT FK_B50A2CB1AC78BCF8 FOREIGN KEY (sport_id) REFERENCES sport (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE competition_bookmaker ADD CONSTRAINT FK_294108C57B39D312 FOREIGN KEY (competition_id) REFERENCES competition (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE competition_bookmaker ADD CONSTRAINT FK_294108C58FB29728 FOREIGN KEY (bookmaker_id) REFERENCES bookmaker (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event_bookmaker ADD CONSTRAINT FK_4347A9A38276ABC FOREIGN KEY (competition_bookmaker_id) REFERENCES competition_bookmaker (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE odd ADD CONSTRAINT FK_F7845EED5F4CDCDF FOREIGN KEY (odds_snapshot_id) REFERENCES odds_snapshot (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE odds_snapshot ADD CONSTRAINT FK_1454814462A19DDB FOREIGN KEY (event_bookmaker_id) REFERENCES event_bookmaker (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE bookmaker_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE competition_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE competition_bookmaker_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE event_bookmaker_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE odd_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE odds_snapshot_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE sport_id_seq CASCADE');
        $this->addSql('ALTER TABLE competition DROP CONSTRAINT FK_B50A2CB1AC78BCF8');
        $this->addSql('ALTER TABLE competition_bookmaker DROP CONSTRAINT FK_294108C57B39D312');
        $this->addSql('ALTER TABLE competition_bookmaker DROP CONSTRAINT FK_294108C58FB29728');
        $this->addSql('ALTER TABLE event_bookmaker DROP CONSTRAINT FK_4347A9A38276ABC');
        $this->addSql('ALTER TABLE odd DROP CONSTRAINT FK_F7845EED5F4CDCDF');
        $this->addSql('ALTER TABLE odds_snapshot DROP CONSTRAINT FK_1454814462A19DDB');
        $this->addSql('DROP TABLE bookmaker');
        $this->addSql('DROP TABLE competition');
        $this->addSql('DROP TABLE competition_bookmaker');
        $this->addSql('DROP TABLE event_bookmaker');
        $this->addSql('DROP TABLE odd');
        $this->addSql('DROP TABLE odds_snapshot');
        $this->addSql('DROP TABLE sport');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
