# Les bases d'Active Directory

[한국어](README.ko.md) | [English](../README.md) | [日本語](README.ja.md) | [简体中文](README.zh-CN.md) | [Español](README.es.md) | **Français**

![Windows Server](https://img.shields.io/badge/Windows_Server-Active_Directory-0078D4?logo=windows&logoColor=white)

Pourquoi utiliser Active Directory (AD) ? Cette page présente le SSO et le sens du mot « annuaire », l'authentification et l'autorisation, les méthodes de chiffrement utiles pour comprendre les échanges AD, et la différence entre un groupe de travail et un domaine.

## Sommaire

- [1. L'objectif principal d'AD](#1-lobjectif-principal-dad)
- [2. Bases de la sécurité : authentification et autorisation](#2-bases-de-la-sécurité--authentification-et-autorisation)
- [3. Protéger les données : le chiffrement](#3-protéger-les-données--le-chiffrement)
- [4. Domaine vs groupe de travail](#4-domaine-vs-groupe-de-travail)
- [5. Une analogie](#5-une-analogie)
- [Résumé](#résumé)

---

## 1. L'objectif principal d'AD

### Authentification unique (SSO)

**La principale raison d'utiliser AD** est de mettre en place un environnement d'**authentification unique (SSO, Single Sign-On)**.

- **Sans AD (groupe de travail)** : avec 10 ordinateurs, il faut créer le compte **séparément** sur les 10, et chaque changement de mot de passe se fait aussi 10 fois.
- **Avec AD (domaine)** : un serveur central, le **contrôleur de domaine (DC)**, gère l'authentification. Un seul compte peut être **conçu pour accéder à toute l'infrastructure de l'entreprise**.
- **À quoi sert AD** : la **gestion des comptes**, l'**authentification** (prouver qui l'on est) et l'**autorisation** (ce que l'on peut faire), le tout de façon **centralisée** en un seul endroit.

![Workgroup vs Domain](../images/ad-workgroup-vs-domain.svg)

### Pourquoi un « annuaire » ?

- **Annuaire (directory)** : ce n'est pas un « dossier » du système de fichiers, mais plutôt un **annuaire téléphonique** — un **magasin qui contient de nombreuses informations** utilisées dans l'entreprise, comme les comptes et les groupes.
- **Pourquoi « Active »** : contrairement à un annuaire papier difficile à modifier, les informations peuvent être **créées, modifiées, changées et supprimées facilement à tout moment** ; on parle donc d'un **« annuaire vivant »**.
- **Avant AD** : jusqu'à Windows NT 4.0, les informations de compte étaient stockées dans la base de données **SAM** (Security Account Manager).

---

## 2. Bases de la sécurité : authentification et autorisation

Ces deux notions sont indispensables pour comprendre la vérification d'identité et le contrôle d'accès dans AD.

### Authentification (Authentication)

- **Définition** : « prouver qui je suis ».
- **Fonctionnement** : quand l'utilisateur saisit son compte et son mot de passe, la demande est envoyée au **contrôleur de domaine (DC)** ; si elle correspond à ce que stocke le DC, celui-ci **confirme** qu'il s'agit bien de cet utilisateur.

### Autorisation (Authorization)

- **Définition** : décider **qui peut faire quelque chose et qui ne le peut pas**.
- Il existe deux types d'autorisation : les **droits (Right)** et les **autorisations d'accès (Permission)**.

| Élément | Droit (Right) | Autorisation d'accès (Permission) |
| --- | --- | --- |
| Définition | Permission de **faire** quelque chose | Permission d'**utiliser** quelque chose |
| Exemples | Arrêter l'ordinateur, y accéder depuis le réseau, se connecter en Bureau à distance | Lire un dossier, écrire un fichier |
| Point clé | Administrator possède tous les droits, d'où son nom de super-compte. | Seul le **propriétaire** de l'objet peut l'accorder. |
| Propriété | Pour modifier les autorisations d'un objet appartenant à quelqu'un d'autre, il faut en plus le droit de **prendre possession**. | — |

---

## 3. Protéger les données : le chiffrement

Ce sont les méthodes de protection des données dans les communications réseau.

![Symmetric, asymmetric and hybrid encryption](../images/ad-encryption.svg)

### Chiffrement à clé symétrique

- **Caractéristique** : **la même clé chiffre et déchiffre**.
- **Avantage** : simple et rapide.
- **Inconvénient** : il faut **transmettre la clé en toute sécurité** à celui qui reçoit les données chiffrées.

### Chiffrement à clé asymétrique

- **Caractéristique** : il y a **deux clés** (asymétrique).
  - **Clé publique (Public Key)** : sert à chiffrer et se partage avec les autres.
  - **Clé privée (Private Key)** : sert à déchiffrer et **ne se partage jamais**.
- **Fonctionnement** : des données chiffrées avec la **clé publique** du destinataire ne peuvent être déchiffrées qu'avec sa **clé privée**.
- **Technologie qui en découle** : les certificats et autorités de certification, c'est-à-dire la **PKI** (Public Key Infrastructure).
- **Avantage** : une sécurité bien plus élevée.

### Signature numérique (Digital Signature)

- **Objectif** : prouver que les données **proviennent bien de cet expéditeur** (identité).
- **Fonctionnement** : l'expéditeur signe avec sa **clé privée**, et le destinataire vérifie avec la **clé publique** de l'expéditeur.

### Chiffrement hybride (mixte, HTTPS)

- La plupart des solutions de chiffrement utilisent une méthode hybride qui combine la **sécurité** des clés asymétriques et la **vitesse** des clés symétriques.
- **Principe** : la **clé symétrique**, l'élément le plus important, est transmise chiffrée par la méthode **asymétrique** sûre, puis **toutes les communications volumineuses qui suivent** utilisent la **clé symétrique**, rapide et efficace.

---

## 4. Domaine vs groupe de travail

Sous Windows, on les distingue selon **qui gère l'authentification**.

| Élément | Groupe de travail (Workgroup) | Domaine (Domain) |
| --- | --- | --- |
| Par défaut | Valeur par défaut à l'installation de Windows. | Mis en place en configurant Active Directory. |
| Qui authentifie | Chaque ordinateur gère localement ses comptes et mots de passe. (authentification locale) | Un système central, le contrôleur de domaine (DC), gère l'authentification. |
| Lien avec AD | Pas connecté à AD. | Se connecter à un domaine s'appelle « joindre le domaine ». |
| Avantages | Simple et géré poste par poste ; adapté aux petits environnements. | SSO ; sécurité, authentification et autorisation sont gérées en un seul endroit. |
| Inconvénients | Il faut saisir un compte et un mot de passe sur chaque ordinateur. | Si le système central (DC) tombe en panne, tout le système d'authentification s'arrête. |

> La différence essentielle est **qui effectue l'authentification de l'identité**.

---

## 5. Une analogie

Imaginez un environnement AD comme un **réseau de bibliothèques centralisé**.

- On s'inscrit une fois à la bibliothèque centrale (**DC**) (**création du compte**) et une seule carte de lecteur (**SSO**) donne accès à toutes les salles de lecture de l'entreprise (**ordinateurs, serveurs**).
- Chaque salle **demande à la bibliothèque centrale** si la carte est valide avant d'ouvrir la porte.
- Si la bibliothèque ferme (**panne du DC**) ou si la carte est annulée (**compte supprimé**), la personne ne peut plus entrer dans aucune salle.

---

## Résumé

- Le but principal d'AD est le **SSO** : gestion des comptes, authentification et autorisation sont traitées **de façon centralisée sur le DC**.
- Un **annuaire** est un « carnet d'adresses » de comptes et de groupes ; **Active** désigne un annuaire « vivant », modifiable à tout moment. Avant AD (NT 4.0), on utilisait la **SAM**.
- L'**authentification**, c'est « qui êtes-vous » ; l'**autorisation**, « que pouvez-vous faire ». L'autorisation se divise en **droits** et **autorisations d'accès**.
- Les **clés symétriques** sont rapides mais leur transmission pose problème ; les **clés asymétriques** sont sûres et servent de base à la **PKI** et à la **signature numérique**. En pratique, on utilise un **hybride** des deux (HTTPS).
- Dans un **groupe de travail**, l'authentification est locale ; dans un **domaine**, c'est le DC qui authentifie. Le domaine est pratique, mais si le DC s'arrête l'authentification aussi ; on rend donc les DC redondants.
