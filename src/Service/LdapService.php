<?php

namespace App\Service;

class LdapService
{
    public $ldaphost;
    public $ldapport;

    public function __construct()
    {
        $this->ldaphost = $_ENV['APP_LDAP_HOST']; //  "ldap://ldap.gendarmerie.fr";
        $this->ldapport = $_ENV['APP_LDAP_PORT']; //  "389";
    }

    public function ldapSearch($filter)
    {
        if (!function_exists('ldap_connect')) {
            return false;
        }

        $ldap_conn = \ldap_connect($this->ldaphost, $this->ldapport) or die("Impossible de se connecter au serveur LDAP $this->ldaphost");
        $sr = ldap_search($ldap_conn, "dc=gendarmerie,dc=defense,dc=gouv,dc=fr", $filter);
        //\ldap_sort($ldap_conn, $sr, 'sn');
        $searchLdapOut = \ldap_get_entries($ldap_conn, $sr);

        if ($searchLdapOut['count'] != 0) {
            return ($searchLdapOut);
        } else {
            return ("0");
        }
    }

    public function get_user_from_ldap($nigend)
    {
        $filter = "(&(objectClass=gendPerson)(employeeNumber=" . ltrim($nigend, '0') . "))";

        $ldap_user = $this->ldapSearch($filter);

        if ($ldap_user === "0" || !$ldap_user)
            return null;

        $user = new \stdClass();
        $user->nigend = $ldap_user[0]['employeenumber'][0];
        $user->nom = $ldap_user[0]['sn'][0];
        $user->prenom = $ldap_user[0]['givenname'][0];
        $user->nigend = $ldap_user[0]['employeenumber'][0];
        $user->unite_id = $ldap_user[0]['codeunite'][0];
        $user->profil = 'USR';

        // recherche du département
        $ldap_unite = $this->get_unite_from_ldap($user->unite_id);
        $unite = $this->format_ldap_unite($ldap_unite);

        $cp = $ldap_unite[0]['postalcode'][0];
        $dpt = self::getDept($cp);
        $user->departement = $dpt;

        $mail_unite = $ldap_user[0]['mailuniteorganique'][0];
        $is_solc = str_starts_with($mail_unite, 'solc') || str_starts_with($mail_unite, 'dsolc');
        $is_csag = str_starts_with($mail_unite, 'csag');
        $is_validateur = str_starts_with($mail_unite, 'comgend');

        if ($is_solc)
            $user->profil = 'SOLC';
        else if ($is_csag)
            $user->profil = 'CSAG';
        else if ($is_validateur)
            $user->profil = 'VDT';

        // if ($ldap_user[0]['poste'][0] === 'chef de groupe en cybermenaces')
        //     $user->roles[] = ['libelle' => 'chef_groupe_solc'];

        return $user;
    }

    public function get_unite_from_ldap($code_unite)
    {
        $filter = "(&(objectclass=organizationalUnit)(codeunite=" . ltrim($code_unite, '0') . "))";
        $ldap_unite = $this->ldapSearch($filter);
        return $ldap_unite;
    }

    public function format_ldap_unite($ldap_unite)
    {
        $unite = new \stdClass();
        $unite->code = $ldap_unite[0]['codeunite'][0];
        $unite->nom = $ldap_unite[0]['description'][0];
        $unite->nom_court = $ldap_unite[0]['businessou'][0];
        $unite->adresse =  $ldap_unite[0]['postaladdress'][0];

        return $unite;
    }

    public function get_unites_sub_from_ldap($code_unite)
    {
        $gpt = 'cn=g_tu-fo_' . $code_unite . ',dmdName=Groupes,dc=gendarmerie,dc=defense,dc=gouv,dc=fr';
        $filter = "(&(objectClass=organizationalunit)(memberof=" . $gpt . "))";
        $unites_sub = $this->ldapSearch($filter);
        return $unites_sub;
    }

    public function get_unites_groupement_from_ldap($groupes_parents, $dept)
    {
        $subs = ['count' => 0];
        foreach ($groupes_parents as $key => $group) {
            if ($key === 'count') {
                continue;
            }
            [$cn] = explode(',', $group);
            if (count(explode('_', $cn)) <= 2) {
                continue;
            }
            [,, $cu] = explode('_', $cn);

            $filter = "(&(objectclass=organizationalUnit)(codeunite=" . $cu . "))";
            $ldap_unite = $this->ldapSearch($filter);

            if ($ldap_unite[0]['businesscategory'][0] === 'REG GEND') {
                continue;
            }

            $adresse = $ldap_unite[0]['postaladdress'][0];

            $explode = explode('$', $adresse);
            while (count($explode) < 4)
                array_unshift($explode, '');


            [,,, $cp_commune] =  $explode;
            [$cp_u] = explode(" ", $cp_commune, 2);

            if ($dept !== $this::getDept($cp_u))
                continue;

            $unites_sub = $this->get_unites_sub_from_ldap($cu);
            if (intval($unites_sub['count']) > intval($subs['count'])) {
                $subs = $unites_sub;
            }
        }

        return $subs;
    }

    public static function getDept($code_postal)
    {
        $isDom = str_starts_with($code_postal, '97');
        $dpt = substr($code_postal, 0, ($isDom ? 3 : 2));
        $dpt = str_pad($dpt, 2, '0', STR_PAD_LEFT);
        return $dpt;
    }

    public static function format_unite($ldap_unite)
    {
        if (!array_key_exists('postaladdress', $ldap_unite))
            return false;

        $unite = new \stdClass();
        $unite->code = $ldap_unite[0]['codeunite'][0];
        $unite->nom = $ldap_unite[0]['description'][0];
        $unite->nom_court = $ldap_unite[0]['businessou'][0];
        $unite->adresse =  $ldap_unite[0]['postaladdress'][0];

        $address = $unite->adresse;
        $explode = explode('$', $address);
        while (count($explode) < 3)
            array_unshift($explode, '');


        if (count($explode) == 4) {
            [$complement, $voie, $complement2, $cp_commune] = $explode;
            $complement .= ', ' . $complement2;
        } else {
            [$complement, $voie, $cp_commune] = $explode;
        }

        $adresse =  new \stdClass();
        $adresse->complement = $complement;
        $adresse->voie = $voie;

        [$cp, $libelle] = explode(" ", $cp_commune, 2);

        $commune = new \stdClass();
        $commune->code_postal = $cp;

        $groupes_parents = $ldap_unite[0]['memberof'];
        $unite->groups = $groupes_parents;

        $commune->libelle = $libelle;

        $pays = new \stdClass();
        $pays->code = 250;
        $pays->alpha2 = 'FR';
        $pays->alpha3 = 'FRA';
        $pays->nom_en_gb = 'France';
        $pays->nom_fr_fr = 'France';

        $adresse->commune = $commune;
        $adresse->pays = $pays;

        $unite->adresse = $adresse;

        return $unite;
    }
}
