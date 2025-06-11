<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250611174335 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE time_sets (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, mons_year DATE NOT NULL, time_set INT NOT NULL, time_spend INT DEFAULT NULL, INDEX IDX_39304CFC19EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE time_sets ADD CONSTRAINT FK_39304CFC19EB6921 FOREIGN KEY (client_id) REFERENCES clients (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE time_sets DROP FOREIGN KEY FK_39304CFC19EB6921
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE time_sets
        SQL);
    }
}
