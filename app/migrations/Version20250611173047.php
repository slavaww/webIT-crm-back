<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250611173047 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE tasks (id INT AUTO_INCREMENT NOT NULL, creator_id INT NOT NULL, client_id INT NOT NULL, status_id INT NOT NULL, worker_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, create_date DATE NOT NULL, start_time DATETIME DEFAULT NULL, end_time DATETIME DEFAULT NULL, INDEX IDX_5058659761220EA6 (creator_id), INDEX IDX_5058659719EB6921 (client_id), INDEX IDX_505865976BF700BD (status_id), INDEX IDX_505865976B20BA36 (worker_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tasks ADD CONSTRAINT FK_5058659761220EA6 FOREIGN KEY (creator_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tasks ADD CONSTRAINT FK_5058659719EB6921 FOREIGN KEY (client_id) REFERENCES clients (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tasks ADD CONSTRAINT FK_505865976BF700BD FOREIGN KEY (status_id) REFERENCES statuses (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tasks ADD CONSTRAINT FK_505865976B20BA36 FOREIGN KEY (worker_id) REFERENCES employee (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE tasks DROP FOREIGN KEY FK_5058659761220EA6
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tasks DROP FOREIGN KEY FK_5058659719EB6921
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tasks DROP FOREIGN KEY FK_505865976BF700BD
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tasks DROP FOREIGN KEY FK_505865976B20BA36
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE tasks
        SQL);
    }
}
