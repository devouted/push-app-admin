<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260402200230 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE notifications (id BINARY(16) NOT NULL, title VARCHAR(255) NOT NULL, body LONGTEXT NOT NULL, image_url VARCHAR(255) DEFAULT NULL, extra_data JSON DEFAULT NULL, is_test TINYINT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, channel_id BINARY(16) NOT NULL, INDEX IDX_6000B0D372F5A1AA (channel_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE notifications ADD CONSTRAINT FK_6000B0D372F5A1AA FOREIGN KEY (channel_id) REFERENCES channels (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notifications DROP FOREIGN KEY FK_6000B0D372F5A1AA');
        $this->addSql('DROP TABLE notifications');
    }
}
