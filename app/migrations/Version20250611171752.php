<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250611171752 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP TABLE statuses
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE clients ADD client_emrg_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE clients ADD CONSTRAINT FK_C82E741E5684F2 FOREIGN KEY (client_emrg_id) REFERENCES clients_emerg (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_C82E741E5684F2 ON clients (client_emrg_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE clients_emerg CHANGE email emal VARCHAR(255) DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE statuses (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE clients DROP FOREIGN KEY FK_C82E741E5684F2
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_C82E741E5684F2 ON clients
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE clients DROP client_emrg_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE clients_emerg CHANGE emal email VARCHAR(255) DEFAULT NULL
        SQL);
    }
}
