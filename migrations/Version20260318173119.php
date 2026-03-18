<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260318173119 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE project (id UUID NOT NULL, name VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE TABLE todo (id UUID NOT NULL, title VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, status VARCHAR(20) NOT NULL, due_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, project_id UUID DEFAULT NULL, assigned_to_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_5A0EB6A0166D1F9C ON todo (project_id)');
        $this->addSql('CREATE INDEX IDX_5A0EB6A0F4BD7827 ON todo (assigned_to_id)');
        $this->addSql('ALTER TABLE todo ADD CONSTRAINT FK_5A0EB6A0166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
        $this->addSql('ALTER TABLE todo ADD CONSTRAINT FK_5A0EB6A0F4BD7827 FOREIGN KEY (assigned_to_id) REFERENCES "user" (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE todo DROP CONSTRAINT FK_5A0EB6A0166D1F9C');
        $this->addSql('ALTER TABLE todo DROP CONSTRAINT FK_5A0EB6A0F4BD7827');
        $this->addSql('DROP TABLE project');
        $this->addSql('DROP TABLE todo');
    }
}
