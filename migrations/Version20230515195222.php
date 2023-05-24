<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230515195222 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE gig ADD creator_id INT NOT NULL');
        $this->addSql('ALTER TABLE gig ADD CONSTRAINT FK_D53020A261220EA6 FOREIGN KEY (creator_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_D53020A261220EA6 ON gig (creator_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE gig DROP FOREIGN KEY FK_D53020A261220EA6');
        $this->addSql('DROP INDEX IDX_D53020A261220EA6 ON gig');
        $this->addSql('ALTER TABLE gig DROP creator_id');
    }
}
