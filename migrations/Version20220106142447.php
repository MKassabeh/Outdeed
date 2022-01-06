<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220106142447 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE candidate (id INT AUTO_INCREMENT NOT NULL, phone_number VARCHAR(20) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, first_name VARCHAR(50) DEFAULT NULL, last_name VARCHAR(50) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE professional_experience (id INT AUTO_INCREMENT NOT NULL, candidate_id INT DEFAULT NULL, job_name VARCHAR(100) NOT NULL, company_name VARCHAR(100) NOT NULL, duration VARCHAR(100) NOT NULL, INDEX IDX_32FDB9BA91BD8781 (candidate_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE skill (id INT AUTO_INCREMENT NOT NULL, candidate_id INT DEFAULT NULL, skill_name VARCHAR(100) NOT NULL, years_of_experience VARCHAR(100) NOT NULL, INDEX IDX_5E3DE47791BD8781 (candidate_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE professional_experience ADD CONSTRAINT FK_32FDB9BA91BD8781 FOREIGN KEY (candidate_id) REFERENCES candidate (id)');
        $this->addSql('ALTER TABLE skill ADD CONSTRAINT FK_5E3DE47791BD8781 FOREIGN KEY (candidate_id) REFERENCES candidate (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE professional_experience DROP FOREIGN KEY FK_32FDB9BA91BD8781');
        $this->addSql('ALTER TABLE skill DROP FOREIGN KEY FK_5E3DE47791BD8781');
        $this->addSql('DROP TABLE candidate');
        $this->addSql('DROP TABLE professional_experience');
        $this->addSql('DROP TABLE skill');
    }
}
