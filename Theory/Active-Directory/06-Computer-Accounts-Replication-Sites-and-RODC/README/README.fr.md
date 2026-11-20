# Comptes d'ordinateur, réplication, sites et RODC

[한국어](README.ko.md) | [English](../README.md) | [日本語](README.ja.md) | [简体中文](README.zh-CN.md) | [Español](README.es.md) | **Français**

![Windows Server](https://img.shields.io/badge/Windows_Server-Active_Directory-0078D4?logo=windows&logoColor=white)

Le comportement des comptes d'ordinateur une fois une machine jointe au domaine, et des conseils pour gérer les comptes AD. Ensuite, la réplication entre DC, les sites et services pour les réseaux lents, et le contrôleur de domaine en lecture seule (RODC) pour les sites peu sûrs.

## Sommaire

- [1. Comptes d'ordinateur et AD](#1-comptes-dordinateur-et-ad)
- [2. Conseils de gestion des comptes AD](#2-conseils-de-gestion-des-comptes-ad)
- [3. Réplication AD](#3-réplication-ad)
- [4. Sites et services](#4-sites-et-services)
- [5. RODC](#5-rodc)
- [Résumé](#résumé)

---

## 1. Comptes d'ordinateur et AD

- Quand un ordinateur rejoint le domaine, un **compte d'ordinateur** est créé automatiquement.
- Un compte d'ordinateur possède lui aussi un **mot de passe**, qui change automatiquement **tous les 30 jours** pour la communication entre l'ordinateur et le DC.
- L'ordinateur et le DC utilisent ce mot de passe pour établir un **canal sécurisé (Secure Channel)** et communiquer.
- Si un ordinateur ne peut pas joindre un DC pendant **plus de 30 jours**, faites-lui utiliser un VPN ou DirectAccess, ou augmentez dans la stratégie de groupe la **durée de vie maximale** du mot de passe du compte d'ordinateur.

---

## 2. Conseils de gestion des comptes AD

- **Où s'applique la stratégie de groupe** : une stratégie de groupe peut être liée aux UO (unités d'organisation), mais pas aux **conteneurs Users** ou **Computers**, où les comptes sont créés par défaut.
- **Changer l'emplacement par défaut** : pour que les nouveaux comptes soient créés dans une UO, utilisez les commandes **`redirusr`** (utilisateurs) et **`redircmp`** (ordinateurs).
- **Outil de traitement en masse (LDIFDE)** : pour **importer** en masse dans AD des propriétés utilisateur comme le service ou le téléphone, ou pour **exporter** des données AD, utilisez **`LDIFDE`**.

```cmd
redirusr "OU=NewUsers,DC=korea,DC=com"
redircmp "OU=NewComputers,DC=korea,DC=com"
```

---

## 3. Réplication AD

### Avec plusieurs DC

- Quand il y a plusieurs DC, **la réplication est continue** pour que tous détiennent les mêmes données.
- **Multimaître** : les données peuvent être modifiées sur **n'importe lequel** des DC en même temps.
- **Objectifs de la réplication** : exactitude, intégrité, cohérence et performances rapides.

### Caractéristiques techniques de la réplication

| Caractéristique | Détails |
| --- | --- |
| Réplication par attribut | Depuis Windows Server 2003, l'unité de réplication est l'**attribut** : des modifications sur des attributs différents d'un même objet fusionnent sans conflit. |
| Réplication par partition | La base AD est répliquée **partition par partition**, et non d'un seul bloc. (schéma, configuration, domaine, GC, application) |
| Topologie automatique | **AD conçoit automatiquement** la façon la plus efficace pour les DC de se répliquer entre eux. |

---

## 4. Sites et services

Par défaut, AD considère que tous les DC se trouvent sur un **réseau rapide** lorsqu'il réplique. Mais si le réseau de l'entreprise est découpé par des **liaisons WAN lentes**, la réplication et l'authentification peuvent poser problème.

Un **site (Site)** regroupe la structure du réseau afin qu'AD contrôle la réplication sur les réseaux lents et oriente les utilisateurs vers **le DC du réseau le plus proche (le plus rapide)**.

![Sites and replication](../images/ad-sites-replication.svg)

### Rôle et composition des sites

| Élément | Détails |
| --- | --- |
| Contrôle de la réplication | Règle quand et à quelle fréquence la réplication a lieu entre sites (sur le WAN) pour alléger la liaison. |
| Localisation des services | Fait en sorte que les clients s'authentifient auprès d'**un DC de leur propre site**. |
| Définition des sites | Les sites sont découpés selon les réseaux bien connectés (sous-réseaux). |
| Lien de sites | Le chemin de réplication qui relie un site à un autre. |
| Coût | Chaque lien de sites reçoit un coût ; les chemins au coût le plus faible sont privilégiés. |

---

## 5. RODC

### Qu'est-ce qu'un RODC ?

- Un DC normal peut **lire et écrire** des données, mais un **RODC (Read-Only Domain Controller)** fonctionne, comme son nom l'indique, en **lecture seule**.
- Il est installé dans des **lieux physiquement peu sûrs** (comme les agences) afin que les informations importantes ne soient pas exposées même si le DC est volé.

![RODC at a branch office](../images/ad-rodc.svg)

### Fonctions de sécurité du RODC

- **Aucun mot de passe stocké** : par défaut, un RODC ne stocke pas d'informations de sécurité sensibles (mots de passe).
- **Mise en cache sélective** : pour de meilleures performances, on peut choisir de mettre en cache (stocker) les mots de passe de **certains utilisateurs** seulement. Cela accélère l'authentification des utilisateurs de ce site tout en préservant la sécurité.

---

## Résumé

- Joindre un domaine crée un **compte d'ordinateur** dont le mot de passe change **tous les 30 jours** et sert à établir un **canal sécurisé** avec le DC.
- On ne peut pas lier de GPO aux **conteneurs Users/Computers** : utilisez `redirusr` et `redircmp` pour déplacer l'emplacement par défaut vers une UO. Pour le travail en masse, utilisez **LDIFDE**.
- La réplication AD est **multimaître**, **par attribut** (depuis 2003) et **par partition**, et la topologie est créée **automatiquement**.
- Les **sites** contrôlent la réplication sur les liaisons WAN lentes et font utiliser aux clients un **DC proche**. Les sites sont reliés par des **liens de sites** et des **coûts**.
- Un **RODC** est un **DC en lecture seule** pour les lieux peu sûrs ; par défaut il ne stocke aucun mot de passe et **ne met en cache que les utilisateurs choisis**.
