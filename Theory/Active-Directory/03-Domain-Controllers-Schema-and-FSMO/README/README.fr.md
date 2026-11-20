# Contrôleurs de domaine, schéma et FSMO

[한국어](README.ko.md) | [English](../README.md) | [日本語](README.ja.md) | [简体中文](README.zh-CN.md) | [Español](README.es.md) | **Français**

![Windows Server](https://img.shields.io/badge/Windows_Server-Active_Directory-0078D4?logo=windows&logoColor=white)

Un regard à l'intérieur du contrôleur de domaine (DC) qui fournit AD. Cette page couvre le schéma et la structure des objets, la base de données, le service d'authentification et les fichiers de stratégie de groupe qui composent un DC, les partitions de la base, le catalogue global et les rôles FSMO à l'échelle de la forêt.

## Sommaire

- [1. Qu'est-ce qu'un contrôleur de domaine ?](#1-quest-ce-quun-contrôleur-de-domaine-)
- [2. Schéma et structure des objets](#2-schéma-et-structure-des-objets)
- [3. Composants d'un DC](#3-composants-dun-dc)
- [4. Partitions de la base de données AD](#4-partitions-de-la-base-de-données-ad)
- [5. Catalogue global (GC)](#5-catalogue-global-gc)
- [6. Rôles FSMO (maîtres d'opérations)](#6-rôles-fsmo-maîtres-dopérations)
- [Résumé](#résumé)

---

## 1. Qu'est-ce qu'un contrôleur de domaine ?

- L'ordinateur qui fournit tout ce qui concerne AD s'appelle un **DC (Domain Controller)**. Les seuls fichiers réellement visibles sur un DC sont ceux de la **base de données**.
- L'authentification utilise le protocole **Kerberos**, et le service qui la traite s'appelle le **service KDC**.
- Il faut **deux contrôleurs de domaine ou plus**.

---

## 2. Schéma et structure des objets

Active Directory repose sur une ossature appelée **schéma (Schema)**.

- **Objets et attributs** : AD est constitué d'objets, et chaque objet possède des attributs.
- **Schéma** : l'ossature de base de ces objets et attributs — **la définition de conception du modèle** — s'appelle le schéma.
- **Même structure partout** : **toute une forêt utilise le même schéma**.
- **Début et fin d'AD** : toute structure d'AD commence et se termine par le schéma.

---

## 3. Composants d'un DC

Un contrôleur de domaine est le serveur qui fournit AD ; il contient le service d'authentification et la base de données.

![Inside a domain controller](../images/ad-dc-components.svg)

| Composant | Détails |
| --- | --- |
| Fichier de base de données AD | Les données AD sont stockées dans le fichier `NTDS.dit` du dossier `C:\Windows\NTDS`. |
| Service d'authentification | Utilise le protocole Kerberos ; le service qui le traite est le **KDC** (Key Distribution Center, centre de distribution de clés). |
| Fichiers de stratégie de groupe | Les fichiers de traitement de la stratégie de groupe sont stockés dans le dossier **SYSVOL**. |

---

## 4. Partitions de la base de données AD

La base de données AD (`NTDS.dit`) est stockée en plusieurs parties, et chaque partition a une **étendue de réplication** différente.

| Partition | Contenu | Étendue |
| --- | --- | --- |
| Schema | Définitions de la structure de base des objets et des attributs | Toute la forêt |
| Configuration | Paramètres qui doivent être identiques dans toute la forêt | Toute la forêt |
| Domain | Données propres au domaine : utilisateurs, groupes, ordinateurs | Ce domaine |
| Application | Espace réservé aux programmes qui utilisent AD (p. ex. DNS) | DC choisis |

---

## 5. Catalogue global (GC)

Le GC est le serveur chargé des **recherches et requêtes de carnet d'adresses dans toute la forêt**.

![Global catalog and FSMO roles](../images/ad-gc-fsmo.svg)

- Il sert à trouver des **comptes d'autres domaines** de la même forêt, ou à rechercher des informations dans toute la forêt.
- Les requêtes GC utilisent le port **3268**, et non le port LDAP habituel 389.
- Pour éviter un trafic réseau inutile, il est recommandé d'avoir **au moins un GC par site**.

---

## 6. Rôles FSMO (maîtres d'opérations)

AD utilise la **réplication multimaître**, mais les opérations importantes qui ne doivent jamais entrer en conflit sont confiées à un **maître unique (Single Master)**. Ces rôles s'appellent les **maîtres d'opérations (FSMO, Flexible Single Master Operations)**.

### Maîtres d'opérations à l'échelle de la forêt

Chacun de ces rôles est tenu par **un seul DC par forêt**.

- **Maître de schéma (Schema Master)** : modifie, met à jour et étend le schéma de toute la forêt.
- **Maître d'attribution de noms de domaine (Domain Naming Master)** : gère la structure des domaines de la forêt. (ajout et suppression de domaines)

---

## Résumé

- Un **DC** est le serveur qui fournit AD ; l'authentification Kerberos est traitée par le **service KDC**. Gardez deux DC ou plus.
- Le **schéma** définit les objets et les attributs, et **toute la forêt utilise le même schéma**.
- Le cœur d'un DC, c'est **`C:\Windows\NTDS\NTDS.dit`** (base de données), le **KDC** (authentification) et **SYSVOL** (fichiers de stratégie de groupe).
- Les partitions de la base sont **Schema et Configuration** (toute la forêt), **Domain** (un domaine) et **Application** (p. ex. DNS).
- Le **GC** gère les recherches dans toute la forêt sur le port **3268** ; prévoyez-en au moins un par site.
- Les opérations qui ne doivent pas entrer en conflit sont confiées à un DC sous forme de rôles **FSMO**. Ceux de la forêt sont le **maître de schéma** et le **maître d'attribution de noms de domaine**.
