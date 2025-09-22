<?php
namespace App\Modele;

use App\Utilitaire\Singleton_ConnexionPDO;
use PDO;
use PDOStatement;

class Modele_EvenementsConsentement
{
    private static function ipToBinary(?string $ip): ?string
    {
        if ($ip === null || $ip === '') {
            return null;
        }
        $packed = @inet_pton($ip);
        return $packed !== false ? $packed : null;
    }

    private static function bindIp(PDOStatement $req, ?string $ip): void
    {
        $ipBin = self::ipToBinary($ip);
        if ($ipBin === null) {
            $req->bindValue('ip_adresse', null, PDO::PARAM_NULL);
        } else {
            $req->bindValue('ip_adresse', $ipBin, PDO::PARAM_LOB);
        }
    }

    
    public static function EvenementsConsentement_Ajouter(int $utilisateur_id, int $finalite_id, string $type_evenement, int $version_politique_id, string $hash_snapshot, ?string $ip_adresse): bool
    {
        $connexionPDO = Singleton_ConnexionPDO::getInstance();
        $sql = 'INSERT INTO `evenements_consentement` (consentement_id, type_evenement, version_politique_id, hash_snapshot, ip_adresse, idUtilisateur)
                VALUES (:consentement_id, :type_evenement, :version_politique_id, :hash_snapshot, :ip_adresse, :idUtilisateur)';
        $req = $connexionPDO->prepare($sql);
        $req->bindValue('consentement_id', $finalite_id, PDO::PARAM_INT);
        $req->bindValue('type_evenement', $type_evenement);
        $req->bindValue('version_politique_id', $version_politique_id, PDO::PARAM_INT);
        $req->bindValue('hash_snapshot', $hash_snapshot);
        self::bindIp($req, $ip_adresse);
        $req->bindValue('idUtilisateur', $utilisateur_id, PDO::PARAM_INT);
        return $req->execute();
    }

    
    public static function EvenementsConsentement_MAJ(int $id, string $type_evenement, int $version_politique_id, string $hash_snapshot, ?string $ip_adresse): bool
    {
        $connexionPDO = Singleton_ConnexionPDO::getInstance();
        $sql = 'UPDATE `evenements_consentement`
                SET type_evenement = :type_evenement, version_politique_id = :version_politique_id, hash_snapshot = :hash_snapshot, ip_adresse = :ip_adresse
                WHERE id = :id';
        $req = $connexionPDO->prepare($sql);
        $req->bindValue('type_evenement', $type_evenement);
        $req->bindValue('version_politique_id', $version_politique_id, PDO::PARAM_INT);
        $req->bindValue('hash_snapshot', $hash_snapshot);
        self::bindIp($req, $ip_adresse);
        $req->bindValue('id', $id, PDO::PARAM_INT);
        return $req->execute();
    }

    
    public static function EvenementsConsentement_Select_All(): array
    {
        $connexionPDO = Singleton_ConnexionPDO::getInstance();
        $req = $connexionPDO->prepare('SELECT * FROM `evenements_consentement` ORDER BY survenu_le DESC, id DESC');
        $req->execute();
        return $req->fetchAll(PDO::FETCH_ASSOC);
    }

    
    public static function EvenementsConsentement_Select_ById(int $id)
    {
        $connexionPDO = Singleton_ConnexionPDO::getInstance();
        $req = $connexionPDO->prepare('SELECT * FROM `evenements_consentement` WHERE id = :id');
        $req->bindValue('id', $id, PDO::PARAM_INT);
        $req->execute();
        $row = $req->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? $row : false;
    }

    
    public static function EvenementsConsentement_Select_ByConsentement(int $finalite_id): array
    {
        $connexionPDO = Singleton_ConnexionPDO::getInstance();
        $req = $connexionPDO->prepare('SELECT * FROM `evenements_consentement` WHERE consentement_id = :consentement_id ORDER BY survenu_le DESC, id DESC');
        $req->bindValue('consentement_id', $finalite_id, PDO::PARAM_INT);
        $req->execute();
        return $req->fetchAll(PDO::FETCH_ASSOC);
    }

    
    public static function EvenementsConsentement_Select_ByUtilisateur(int $utilisateur_id): array
    {
        $connexionPDO = Singleton_ConnexionPDO::getInstance();
        $req = $connexionPDO->prepare('SELECT * FROM `evenements_consentement` WHERE idUtilisateur = :idUtilisateur ORDER BY survenu_le DESC, id DESC');
        $req->bindValue('idUtilisateur', $utilisateur_id, PDO::PARAM_INT);
        $req->execute();
        return $req->fetchAll(PDO::FETCH_ASSOC);
    }
}
