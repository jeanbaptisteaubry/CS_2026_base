<?php
namespace App\Modele;

use App\Utilitaire\Singleton_ConnexionPDO;
use PDO;
use PDOStatement;

class Modele_Consentements
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

    
    public static function Consentements_Ajouter(int $utilisateur_id, int $finalite_id, string $statut, int $version_politique_id, ?string $ip_adresse): bool
    {
        $connexionPDO = Singleton_ConnexionPDO::getInstance();
        $sql = 'INSERT INTO `consentements` (utilisateur_id, finalite_id, statut, version_politique_id, ip_adresse)
                VALUES (:utilisateur_id, :finalite_id, :statut, :version_politique_id, :ip_adresse)
                ON DUPLICATE KEY UPDATE statut = VALUES(statut), version_politique_id = VALUES(version_politique_id), ip_adresse = VALUES(ip_adresse), cree_le = CURRENT_TIMESTAMP';
        $req = $connexionPDO->prepare($sql);
        $req->bindValue('utilisateur_id', $utilisateur_id, PDO::PARAM_INT);
        $req->bindValue('finalite_id', $finalite_id, PDO::PARAM_INT);
        $req->bindValue('statut', $statut);
        $req->bindValue('version_politique_id', $version_politique_id, PDO::PARAM_INT);
        self::bindIp($req, $ip_adresse);
        return $req->execute();
    }

    
    public static function Consentements_MAJ(int $utilisateur_id, int $finalite_id, string $statut, int $version_politique_id, ?string $ip_adresse): bool
    {
        $connexionPDO = Singleton_ConnexionPDO::getInstance();
        $sql = 'UPDATE `consentements`
                SET statut = :statut, version_politique_id = :version_politique_id, ip_adresse = :ip_adresse, cree_le = CURRENT_TIMESTAMP
                WHERE utilisateur_id = :utilisateur_id AND finalite_id = :finalite_id';
        $req = $connexionPDO->prepare($sql);
        $req->bindValue('statut', $statut);
        $req->bindValue('version_politique_id', $version_politique_id, PDO::PARAM_INT);
        self::bindIp($req, $ip_adresse);
        $req->bindValue('utilisateur_id', $utilisateur_id, PDO::PARAM_INT);
        $req->bindValue('finalite_id', $finalite_id, PDO::PARAM_INT);
        return $req->execute();
    }

    
    public static function Consentements_Select_All(): array
    {
        $connexionPDO = Singleton_ConnexionPDO::getInstance();
        $req = $connexionPDO->prepare('SELECT * FROM `consentements` ORDER BY cree_le DESC, utilisateur_id, finalite_id');
        $req->execute();
        return $req->fetchAll(PDO::FETCH_ASSOC);
    }

    
    public static function Consentements_Select_ByCle(int $utilisateur_id, int $finalite_id)
    {
        $connexionPDO = Singleton_ConnexionPDO::getInstance();
        $req = $connexionPDO->prepare('SELECT * FROM `consentements`
                WHERE utilisateur_id = :utilisateur_id AND finalite_id = :finalite_id');
        $req->bindValue('utilisateur_id', $utilisateur_id, PDO::PARAM_INT);
        $req->bindValue('finalite_id', $finalite_id, PDO::PARAM_INT);
        $req->execute();
        $row = $req->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? $row : false;
    }

    
    public static function Consentements_Select_Dernier_ByUtilisateur_Finalite(int $utilisateur_id, int $finalite_id)
    {
        return self::Consentements_Select_ByCle($utilisateur_id, $finalite_id);
    }

    
    public static function Consentements_Select_Dernier_ByIp_Finalite(string $ip_adresse, int $finalite_id)
    {
        $connexionPDO = Singleton_ConnexionPDO::getInstance();
        $ipBin = self::ipToBinary($ip_adresse);
        if ($ipBin === null) {
            return false;
        }
        $req = $connexionPDO->prepare('SELECT * FROM `consentements`
                WHERE ip_adresse = :ip_adresse AND finalite_id = :finalite_id
                ORDER BY cree_le DESC LIMIT 1');
        $req->bindValue('ip_adresse', $ipBin, PDO::PARAM_LOB);
        $req->bindValue('finalite_id', $finalite_id, PDO::PARAM_INT);
        $req->execute();
        $row = $req->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? $row : false;
    }
}



