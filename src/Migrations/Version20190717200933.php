<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190717200933 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE forum_topics (id INT AUTO_INCREMENT NOT NULL, author_id INT DEFAULT NULL, subcategory_id INT NOT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_895975E8F675F31B (author_id), INDEX IDX_895975E85DC6FE57 (subcategory_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE forum_topics ADD CONSTRAINT FK_895975E8F675F31B FOREIGN KEY (author_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE forum_topics ADD CONSTRAINT FK_895975E85DC6FE57 FOREIGN KEY (subcategory_id) REFERENCES forum_subcategories (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE forum_topics');
    }
}
