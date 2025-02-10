<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250210011220 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE bookmaker_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE event_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE odd_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE odds_snapshot_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE bookmaker (id INT NOT NULL, name VARCHAR(100) NOT NULL, slug VARCHAR(50) NOT NULL, website VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN bookmaker.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN bookmaker.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE event (id INT NOT NULL, bookmaker_id INT NOT NULL, bookmaker_event_id VARCHAR(50) NOT NULL, sport VARCHAR(50) NOT NULL, competition VARCHAR(100) NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, participant1 VARCHAR(100) NOT NULL, participant2 VARCHAR(100) NOT NULL, status VARCHAR(20) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3BAE0AA78FB29728 ON event (bookmaker_id)');
        $this->addSql('COMMENT ON COLUMN event.date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN event.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN event.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE odd (id INT NOT NULL, odds_snapshot_id INT NOT NULL, outcome VARCHAR(100) NOT NULL, value NUMERIC(6, 2) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F7845EED5F4CDCDF ON odd (odds_snapshot_id)');
        $this->addSql('COMMENT ON COLUMN odd.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE odds_snapshot (id INT NOT NULL, event_id INT NOT NULL, snapshot_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, market VARCHAR(50) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_1454814471F7E88B ON odds_snapshot (event_id)');
        $this->addSql('COMMENT ON COLUMN odds_snapshot.snapshot_date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN odds_snapshot.created_at IS \'(DC2Type:datetime_immutable)\'');
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
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA78FB29728 FOREIGN KEY (bookmaker_id) REFERENCES bookmaker (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE odd ADD CONSTRAINT FK_F7845EED5F4CDCDF FOREIGN KEY (odds_snapshot_id) REFERENCES odds_snapshot (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE odds_snapshot ADD CONSTRAINT FK_1454814471F7E88B FOREIGN KEY (event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE bookmaker_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE event_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE odd_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE odds_snapshot_id_seq CASCADE');
        $this->addSql('ALTER TABLE event DROP CONSTRAINT FK_3BAE0AA78FB29728');
        $this->addSql('ALTER TABLE odd DROP CONSTRAINT FK_F7845EED5F4CDCDF');
        $this->addSql('ALTER TABLE odds_snapshot DROP CONSTRAINT FK_1454814471F7E88B');
        $this->addSql('DROP TABLE bookmaker');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE odd');
        $this->addSql('DROP TABLE odds_snapshot');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
