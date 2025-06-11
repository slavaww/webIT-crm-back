<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250611175139 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE time_spend (id INT AUTO_INCREMENT NOT NULL, client_id INT DEFAULT NULL, worker_id INT NOT NULL, task_id INT NOT NULL, comment_id INT DEFAULT NULL, date DATETIME NOT NULL, time_spend INT NOT NULL, INDEX IDX_A9A0C64119EB6921 (client_id), INDEX IDX_A9A0C6416B20BA36 (worker_id), INDEX IDX_A9A0C6418DB60186 (task_id), INDEX IDX_A9A0C641F8697D13 (comment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE time_spend ADD CONSTRAINT FK_A9A0C64119EB6921 FOREIGN KEY (client_id) REFERENCES clients (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE time_spend ADD CONSTRAINT FK_A9A0C6416B20BA36 FOREIGN KEY (worker_id) REFERENCES employee (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE time_spend ADD CONSTRAINT FK_A9A0C6418DB60186 FOREIGN KEY (task_id) REFERENCES tasks (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE time_spend ADD CONSTRAINT FK_A9A0C641F8697D13 FOREIGN KEY (comment_id) REFERENCES comments (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE time_spend DROP FOREIGN KEY FK_A9A0C64119EB6921
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE time_spend DROP FOREIGN KEY FK_A9A0C6416B20BA36
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE time_spend DROP FOREIGN KEY FK_A9A0C6418DB60186
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE time_spend DROP FOREIGN KEY FK_A9A0C641F8697D13
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE time_spend
        SQL);
    }
}
