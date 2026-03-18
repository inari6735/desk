<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260318183600 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE project ADD budget_hours NUMERIC(8, 1) DEFAULT NULL');
        $this->addSql('ALTER TABLE todo ADD estimated_hours NUMERIC(7, 1) DEFAULT NULL');
        $this->addSql('ALTER TABLE todo ADD spent_hours NUMERIC(7, 1) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE project DROP budget_hours');
        $this->addSql('ALTER TABLE todo DROP estimated_hours');
        $this->addSql('ALTER TABLE todo DROP spent_hours');
    }
}
