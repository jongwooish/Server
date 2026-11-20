# Zones DNS, outils et AGDLP

[한국어](README.ko.md) | [English](../README.md) | [日本語](README.ja.md) | [简体中文](README.zh-CN.md) | [Español](README.es.md) | **Français**

![Windows Server](https://img.shields.io/badge/Windows_Server-Active_Directory-0078D4?logo=windows&logoColor=white)

Un rappel sur les zones DNS, les enregistrements et les mises à jour dynamiques — le cœur d'AD — et sur l'installation d'un contrôleur de domaine supplémentaire (y compris Server Core). Puis les outils d'administration d'AD et la corbeille, les types et étendues de groupes, et la stratégie de groupes AGDLP.

## Sommaire

- [1. Zones DNS et principaux enregistrements](#1-zones-dns-et-principaux-enregistrements)
- [2. Ajouter un autre contrôleur de domaine](#2-ajouter-un-autre-contrôleur-de-domaine)
- [3. Outils d'administration et corbeille AD](#3-outils-dadministration-et-corbeille-ad)
- [4. Types et étendues de groupes](#4-types-et-étendues-de-groupes)
- [5. La stratégie de groupes AGDLP](#5-la-stratégie-de-groupes-agdlp)
- [Résumé](#résumé)

---

## 1. Zones DNS et principaux enregistrements

Le DNS est essentiel à AD, il faut donc bien le comprendre. Un serveur DNS gère les noms en créant des **zones (Zone)**.

### Recherche directe et inversée

| Recherche | Rôle | Exemple |
| --- | --- | --- |
| Recherche directe | Nom de domaine → adresse IP | `www.korea.com` → `192.168.0.1` |
| Recherche inversée | Adresse IP → nom de domaine | `192.168.0.1` → `www.korea.com` |

### Types de zones DNS

![DNS zone types](../images/ad-dns-zones.svg)

1. **Zone principale (Primary Zone)** : la zone principale qui convertit les noms en IP. Elle est accessible en lecture et en écriture.
2. **Zone secondaire (Secondary Zone)** : une **copie de sauvegarde en lecture seule** de la zone principale.
3. **Zone de stub (Stub Zone)** : ne contient **que les enregistrements NS** indiquant où la zone est servie et les **enregistrements A** de ces serveurs.

### Principaux enregistrements DNS

| Enregistrement | Rôle |
| --- | --- |
| A | L'enregistrement le plus simple : associe un nom à une adresse IP. |
| CNAME | Un **alias** qui pointe vers un autre nom (un enregistrement A). |
| MX | Indique l'adresse du **serveur de messagerie** du domaine. |
| PTR | Créé dans une zone de recherche inversée pour associer une adresse IP à un nom. |

### Mise à jour dynamique (Dynamic Update)

Fonction par laquelle les clients **inscrivent eux-mêmes leur nom d'hôte dans le DNS**.

- Elle est peu utilisée dans un DNS classique, mais AD utilise les **mises à jour dynamiques sécurisées**, qui appliquent une sécurité lors de l'inscription des clients.
- Les **mises à jour dynamiques non sécurisées** peuvent accumuler des données parasites ; mieux vaut ne pas les utiliser.

---

## 2. Ajouter un autre contrôleur de domaine

Avec un seul DC, l'authentification s'arrête quand ce DC tombe. C'est pourquoi, en pratique, on recommande **deux DC ou plus**.

### Utiliser Server Core

- Un contrôleur de domaine AD peut aussi être installé sur **Windows Server Core**, plus léger et nécessitant moins de correctifs.
- Server Core n'a pas d'outils graphiques : tout se fait avec **PowerShell** ou des commandes. Utilisez **`sconfig`** pour la configuration de base.
- Un serveur Core peut aussi être administré à distance depuis le **Gestionnaire de serveur** d'un serveur avec interface graphique.

```powershell
# Installer le rôle AD DS
Install-WindowsFeature -Name AD-Domain-Services -IncludeManagementTools

# Ajouter comme DC à un domaine existant (promotion)
Install-ADDomainController -DomainName "korea.com" -InstallDns
```

> **Astuce** : en GUI comme en CLI, avant la promotion en DC, configurez le **DNS pour qu'il pointe vers un DC existant**.

---

## 3. Outils d'administration et corbeille AD

### Outils d'administration

Il existe deux outils pour créer des utilisateurs et des groupes.

1. **Utilisateurs et ordinateurs Active Directory (ADUC)** : l'outil historique.
2. **Centre d'administration Active Directory (ADAC)** : l'outil plus récent. Il **affiche la commande PowerShell** de chaque action, ce qui est pratique pour écrire des scripts plus tard.

### Corbeille AD

- Autrefois, un objet AD supprimé par erreur **ne pouvait être récupéré qu'à partir d'une sauvegarde**.
- Depuis Windows Server 2008 R2, l'activation de la **corbeille AD** permet de restaurer facilement des utilisateurs ou des groupes supprimés.

---

## 4. Types et étendues de groupes

Bien utiliser AD, c'est bien utiliser les groupes. En plaçant les utilisateurs dans des groupes et en attribuant les droits aux groupes, tout se gère de façon centralisée.

### Types de groupes

| Type | Usage |
| --- | --- |
| Groupe de distribution | Une liste pour envoyer des e-mails. Il **n'a pas d'identificateur de sécurité (SID)** et ne peut donc pas recevoir d'autorisations. |
| Groupe de sécurité | Sert à **accorder des autorisations**. Il peut aussi servir pour les e-mails. |

### Étendues de groupes

| Étendue | Usage |
| --- | --- |
| Groupe global | Pour **regrouper des personnes**. |
| Groupe de domaine local | Pour **accorder des autorisations**. |
| Groupe universel | Couvre **toute la forêt**. Pratique pour regrouper des groupes globaux quand il y a plusieurs domaines. |

---

## 5. La stratégie de groupes AGDLP

AGDLP n'accorde pas les autorisations directement aux utilisateurs, mais **étape par étape à travers des groupes**. Elle permet de gérer toutes les autorisations dans AD, ce qui est l'une des principales raisons d'utiliser AD.

![AGDLP](../images/ad-agdlp.svg)

- **A** - Account : créer les comptes utilisateurs.
- **G** - Global Group : placer les utilisateurs dans un groupe global.
- **DL** - Domain Local Group : placer le groupe global dans un groupe de domaine local.
- **P** - Permission : accorder l'autorisation au groupe de domaine local.

---

## Résumé

- Les zones DNS sont **principale** (lecture/écriture), **secondaire** (sauvegarde en lecture seule) et **de stub** (NS + A uniquement).
- Principaux enregistrements : **A** (nom→IP), **CNAME** (alias), **MX** (serveur de messagerie), **PTR** (IP→nom). AD utilise les **mises à jour dynamiques sécurisées**.
- Gardez **deux DC ou plus**. Sur Server Core, utilisez `sconfig`, `Install-WindowsFeature` et `Install-ADDomainController`, et **pointez le DNS vers un DC existant** avant la promotion.
- Les outils sont **ADUC** et **ADAC** ; ADAC affiche les commandes PowerShell. Activer la **corbeille AD** permet de restaurer facilement les objets supprimés.
- Les types de groupes sont **distribution** (sans SID) et **sécurité** ; les étendues sont **globale** (personnes), **domaine local** (autorisations) et **universelle** (toute la forêt).
- **AGDLP** : compte → groupe global → groupe de domaine local → autorisation.
