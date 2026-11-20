# Composants d'AD DS et LDAP

[한국어](README.ko.md) | [English](../README.md) | [日本語](README.ja.md) | [简体中文](README.zh-CN.md) | [Español](README.es.md) | **Français**

![Windows Server](https://img.shields.io/badge/Windows_Server-Active_Directory-0078D4?logo=windows&logoColor=white)

Les protocoles qu'AD utilise pour l'authentification (Kerberos) et pour la consultation d'informations (LDAP), ainsi que les composants physiques et logiques qui forment AD. Ensuite, les notions de domaine, d'UO, de DN et de forêt, avec des schémas.

## Sommaire

- [1. Termes et protocoles de base](#1-termes-et-protocoles-de-base)
- [2. Composants d'AD](#2-composants-dad)
- [3. Domaines AD DS](#3-domaines-ad-ds)
- [4. UO et noms uniques](#4-uo-et-noms-uniques)
- [5. LDAP et outils de requête](#5-ldap-et-outils-de-requête)
- [6. Forêts AD DS](#6-forêts-ad-ds)
- [Résumé](#résumé)

---

## 1. Termes et protocoles de base

### Termes

| Anglais | Signification |
| --- | --- |
| Attribute | Attribut |
| Object | Objet |
| department | Attribut du nom du service |
| displayName | Attribut du nom d'affichage |

### Kerberos pour l'authentification, LDAP pour les requêtes

- Active Directory utilise le protocole **Kerberos** pour l'authentification, sur le port **88**.
- AD ne sert pas qu'à l'authentification ; il peut aussi servir de **base de données d'informations**.
- Pour **interroger** quelque chose, on utilise le protocole **LDAP**, sur le port **389**.
- En bref, AD est une base de données : ses données servent à l'**authentification**, et l'on peut aussi en extraire des informations par des **requêtes**.

---

## 2. Composants d'AD

AD comporte des **composants physiques**, visibles, et des **composants logiques**, conceptuels. Le serveur qui gère l'authentification dans AD s'appelle un **contrôleur de domaine (Domain Controller, DC)**.

![AD DS components](../images/ad-components.svg)

### Composants physiques

- **Data store** : les fichiers de base de données visibles sur le DC.
- **Domain controllers** : les serveurs qui gèrent l'authentification.
- **Global catalog server** : un DC qui joue un rôle particulier s'appelle un GC.
- **RODC** : un contrôleur de domaine en lecture seule.

### Composants logiques

- **Partitions** : la base de données AD est divisée en interne selon l'usage ; chaque partie est une partition.
- **Schema** : les définitions des objets et des attributs (Attribute).
- **Domains** : une limite de gestion et d'authentification.
- **Domain trees** : un arbre formé de plusieurs domaines reliés.
- **Forests** : un ensemble de plusieurs arbres de domaines.
- **Sites** : représentent un découpage du réseau.
- **OUs** : unités d'organisation — une limite découpée selon un besoin.

---

## 3. Domaines AD DS

- Un domaine est **la plus grande étendue qu'un contrôleur de domaine peut authentifier**.
- Pour créer un domaine, il faut **au moins un contrôleur de domaine**.
- Le contrôleur de domaine stocke les informations du domaine **objet par objet**.
- AD recommande **deux contrôleurs de domaine ou plus**. (p. ex. répartition de charge, tolérance aux pannes)
- Un administrateur du domaine dispose de **tous les droits** dans le domaine.
- Le domaine est **la plus petite limite** d'AD.

---

## 4. UO et noms uniques

### UO (Organizational Unit)

Une UO permet de **découper logiquement un domaine en parties**.

![OUs inside a domain](../images/ad-ou-structure.svg)

- Elle subdivise le domaine pour faciliter l'administration.
- C'est aussi **l'unité de déploiement de la stratégie de groupe (GPO)**.

### Nom unique (Distinguished Name, DN)

Le nom qui **identifie un objet de façon unique** dans AD est son nom unique. On l'écrit de l'objet du bas jusqu'au domaine du haut.

- `OU=Sales,DC=KOALRA,DC=COM` : l'UO Sales du domaine KOALRA.COM
- `CN=Koalra,OU=Marketing,DC=KOALRA,DC=COM` : l'objet Koalra dans l'UO Marketing

---

## 5. LDAP et outils de requête

AD est une base de données. **LDAP (Lightweight Directory Access Protocol)** est un protocole de service d'annuaire permettant de gérer et de consulter de façon centralisée et efficace des informations comme les utilisateurs, les groupes et les appareils du réseau.

AD peut être interrogé d'emblée ; ces outils permettent de le faire :

- **Outils d'administration d'Active Directory** (Utilisateurs et ordinateurs, etc.)
- **LDP** (`ldp.exe`)
- **Modification ADSI** (`ADSIEDIT.MSC`)

---

## 6. Forêts AD DS

AD peut comporter plusieurs domaines, et ces domaines reliés entre eux forment une **forêt (Forest)**.

![Domains, trees and a forest](../images/ad-forest.svg)

- Une forêt est **un ensemble de domaines ayant la même structure d'attributs et d'objets**.
- Avoir la même structure se dit **partager le même schéma (Schema)**.
- Des domaines réunis dans un même espace de noms contigu (p. ex. `sales.koalra.com` sous `koalra.com`) forment un **arbre de domaines**, et plusieurs arbres forment une forêt.

---

## Résumé

- AD est une base de données qui authentifie avec **Kerberos (88)** et s'interroge avec **LDAP (389)**.
- Composants physiques : **data store, DC, GC, RODC**. Composants logiques : **partitions, schéma, domaines, arbres de domaines, forêts, sites, UO**.
- Un **domaine** est la plus grande étendue qu'un DC peut authentifier et la plus petite limite d'AD ; deux DC ou plus sont recommandés.
- Une **UO** découpe logiquement un domaine et constitue **l'unité à laquelle s'applique la stratégie de groupe**.
- Un **DN** identifie un objet de façon unique sous la forme `CN=…,OU=…,DC=…`.
- Une **forêt** est un ensemble de domaines (arbres) partageant le même schéma.
