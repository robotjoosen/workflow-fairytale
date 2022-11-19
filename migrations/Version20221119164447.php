<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221119164447 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add workflow to give task proper name';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE task ADD COLUMN workflow VARCHAR(255) DEFAULT \'\' NOT NULL');
        $this->addSql('UPDATE task SET workflow = name');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__task AS SELECT id, quest_id, name, state, ordinality FROM task');
        $this->addSql('DROP TABLE task');
        $this->addSql('CREATE TABLE task (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, quest_id INTEGER NOT NULL, name VARCHAR(255) NOT NULL, state VARCHAR(255) NOT NULL, ordinality INTEGER DEFAULT 0 NOT NULL, CONSTRAINT FK_527EDB25209E9EF4 FOREIGN KEY (quest_id) REFERENCES quest (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO task (id, quest_id, name, state, ordinality) SELECT id, quest_id, name, state, ordinality FROM __temp__task');
        $this->addSql('DROP TABLE __temp__task');
        $this->addSql('CREATE INDEX IDX_527EDB25209E9EF4 ON task (quest_id)');
    }
}
