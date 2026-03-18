<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260318231845 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE task_group (id UUID NOT NULL, name VARCHAR(100) NOT NULL, color VARCHAR(7) DEFAULT NULL, position INT NOT NULL, project_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_AA645FE5166D1F9C ON task_group (project_id)');
        $this->addSql('CREATE TABLE task_group_todo (task_group_id UUID NOT NULL, todo_id UUID NOT NULL, PRIMARY KEY (task_group_id, todo_id))');
        $this->addSql('CREATE INDEX IDX_6F47A67FBE94330B ON task_group_todo (task_group_id)');
        $this->addSql('CREATE INDEX IDX_6F47A67FEA1EBC33 ON task_group_todo (todo_id)');
        $this->addSql('ALTER TABLE task_group ADD CONSTRAINT FK_AA645FE5166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE task_group_todo ADD CONSTRAINT FK_6F47A67FBE94330B FOREIGN KEY (task_group_id) REFERENCES task_group (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE task_group_todo ADD CONSTRAINT FK_6F47A67FEA1EBC33 FOREIGN KEY (todo_id) REFERENCES todo (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE task_group DROP CONSTRAINT FK_AA645FE5166D1F9C');
        $this->addSql('ALTER TABLE task_group_todo DROP CONSTRAINT FK_6F47A67FBE94330B');
        $this->addSql('ALTER TABLE task_group_todo DROP CONSTRAINT FK_6F47A67FEA1EBC33');
        $this->addSql('DROP TABLE task_group');
        $this->addSql('DROP TABLE task_group_todo');
    }
}
