<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260402203905 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE subscriptions (id BINARY(16) NOT NULL, subscribed_at DATETIME NOT NULL, last_active_at DATETIME DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, consumer_id BINARY(16) NOT NULL, channel_id BINARY(16) NOT NULL, INDEX IDX_4778A0137FDBD6D (consumer_id), INDEX IDX_4778A0172F5A1AA (channel_id), UNIQUE INDEX UNIQ_4778A0137FDBD6D72F5A1AA (consumer_id, channel_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE subscriptions ADD CONSTRAINT FK_4778A0137FDBD6D FOREIGN KEY (consumer_id) REFERENCES consumers (id)');
        $this->addSql('ALTER TABLE subscriptions ADD CONSTRAINT FK_4778A0172F5A1AA FOREIGN KEY (channel_id) REFERENCES channels (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE subscriptions DROP FOREIGN KEY FK_4778A0137FDBD6D');
        $this->addSql('ALTER TABLE subscriptions DROP FOREIGN KEY FK_4778A0172F5A1AA');
        $this->addSql('DROP TABLE subscriptions');
    }
}
