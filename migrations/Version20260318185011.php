<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260318185011 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE time_entry (id UUID NOT NULL, hours NUMERIC(6, 1) NOT NULL, note TEXT DEFAULT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, todo_id UUID NOT NULL, user_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_6E537C0CEA1EBC33 ON time_entry (todo_id)');
        $this->addSql('CREATE INDEX IDX_6E537C0CA76ED395 ON time_entry (user_id)');
        $this->addSql('ALTER TABLE time_entry ADD CONSTRAINT FK_6E537C0CEA1EBC33 FOREIGN KEY (todo_id) REFERENCES todo (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE time_entry ADD CONSTRAINT FK_6E537C0CA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE time_entry DROP CONSTRAINT FK_6E537C0CEA1EBC33');
        $this->addSql('ALTER TABLE time_entry DROP CONSTRAINT FK_6E537C0CA76ED395');
        $this->addSql('DROP TABLE time_entry');
    }
}
