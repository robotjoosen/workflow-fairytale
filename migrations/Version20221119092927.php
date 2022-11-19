<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221119092927 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add ordinality to quest and task';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE quest ADD COLUMN ordinality INTEGER DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE task ADD COLUMN ordinality INTEGER DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__quest AS SELECT id, hero_id, name, description FROM quest');
        $this->addSql('DROP TABLE quest');
        $this->addSql('CREATE TABLE quest (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, hero_id INTEGER NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, CONSTRAINT FK_4317F81745B0BCD FOREIGN KEY (hero_id) REFERENCES hero (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO quest (id, hero_id, name, description) SELECT id, hero_id, name, description FROM __temp__quest');
        $this->addSql('DROP TABLE __temp__quest');
        $this->addSql('CREATE INDEX IDX_4317F81745B0BCD ON quest (hero_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__task AS SELECT id, quest_id, name, state FROM task');
        $this->addSql('DROP TABLE task');
        $this->addSql('CREATE TABLE task (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, quest_id INTEGER NOT NULL, name VARCHAR(255) NOT NULL, state VARCHAR(255) NOT NULL, CONSTRAINT FK_527EDB25209E9EF4 FOREIGN KEY (quest_id) REFERENCES quest (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO task (id, quest_id, name, state) SELECT id, quest_id, name, state FROM __temp__task');
        $this->addSql('DROP TABLE __temp__task');
        $this->addSql('CREATE INDEX IDX_527EDB25209E9EF4 ON task (quest_id)');
    }
}
