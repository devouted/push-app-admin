<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260402184143 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE channels (id BINARY(16) NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, category VARCHAR(100) DEFAULT NULL, icon VARCHAR(255) DEFAULT NULL, language VARCHAR(5) DEFAULT \'pl\' NOT NULL, status VARCHAR(20) DEFAULT \'active\' NOT NULL, blocked_reason LONGTEXT DEFAULT NULL, is_public TINYINT DEFAULT 1 NOT NULL, max_subscribers INT DEFAULT NULL, inactivity_timeout_days INT DEFAULT 7 NOT NULL, api_key VARCHAR(64) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, owner_id INT NOT NULL, UNIQUE INDEX UNIQ_F314E2B6C912ED9D (api_key), INDEX IDX_F314E2B67E3C61F9 (owner_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE channels ADD CONSTRAINT FK_F314E2B67E3C61F9 FOREIGN KEY (owner_id) REFERENCES users (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE channels DROP FOREIGN KEY FK_F314E2B67E3C61F9');
        $this->addSql('DROP TABLE channels');
    }
}
