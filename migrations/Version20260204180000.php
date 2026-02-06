<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Ajoute le champ is_active à la table user pour permettre à l'admin
 * de désactiver un compte (l'utilisateur ne pourra plus se connecter).
 */
final class Version20260204180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add is_active to user table for admin account enable/disable';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` ADD is_active TINYINT(1) DEFAULT 1 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` DROP is_active');
    }
}
