<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190813173744 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE forum_categories ADD slug VARCHAR(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B8C5FB2D5E237E06 ON forum_categories (name)');
        $this->addSql('ALTER TABLE forum_subcategories ADD slug VARCHAR(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8DE2AB0E5E237E06 ON forum_subcategories (name)');
        $this->addSql('ALTER TABLE forum_topics ADD slug VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE users ADD slug VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX UNIQ_B8C5FB2D5E237E06 ON forum_categories');
        $this->addSql('ALTER TABLE forum_categories DROP slug');
        $this->addSql('DROP INDEX UNIQ_8DE2AB0E5E237E06 ON forum_subcategories');
        $this->addSql('ALTER TABLE forum_subcategories DROP slug');
        $this->addSql('ALTER TABLE forum_topics DROP slug');
        $this->addSql('ALTER TABLE users DROP slug');
    }
}
