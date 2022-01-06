<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220106101605 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE job ADD published_by_id INT NOT NULL');
        $this->addSql('ALTER TABLE job ADD CONSTRAINT FK_FBD8E0F85B075477 FOREIGN KEY (published_by_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_FBD8E0F85B075477 ON job (published_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE job DROP FOREIGN KEY FK_FBD8E0F85B075477');
        $this->addSql('DROP INDEX IDX_FBD8E0F85B075477 ON job');
        $this->addSql('ALTER TABLE job DROP published_by_id');
    }
}
