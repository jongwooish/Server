# Stratégie de groupe

[한국어](README.ko.md) | [English](../README.md) | [日本語](README.ja.md) | [简体中文](README.zh-CN.md) | [Español](README.es.md) | **Français**

![Windows Server](https://img.shields.io/badge/Windows_Server-Active_Directory-0078D4?logo=windows&logoColor=white)

La stratégie de groupe (Group Policy), seconde grande raison d'utiliser AD. Cette page couvre ce qu'elle permet de faire, sa structure et les endroits où la lier, GPC et GPT, la différence entre GPO et GPP, les intervalles d'actualisation, l'ordre de traitement et le filtrage, ainsi que les outils de vérification des résultats.

## Sommaire

- [1. Qu'est-ce que la stratégie de groupe ?](#1-quest-ce-que-la-stratégie-de-groupe-)
- [2. Structure et étendue de liaison](#2-structure-et-étendue-de-liaison)
- [3. Fonctionnement de la stratégie de groupe](#3-fonctionnement-de-la-stratégie-de-groupe)
- [4. Stratégie de groupe (GPO) vs préférences (GPP)](#4-stratégie-de-groupe-gpo-vs-préférences-gpp)
- [5. Ordre de traitement et filtrage](#5-ordre-de-traitement-et-filtrage)
- [6. Vérifier les résultats et modèles d'administration](#6-vérifier-les-résultats-et-modèles-dadministration)
- [Résumé](#résumé)

---

## 1. Qu'est-ce que la stratégie de groupe ?

Il y a deux grandes raisons d'utiliser AD, et la stratégie de groupe en est une.

1. **Authentification centralisée (SSO)** : une seule ouverture de session règle tout.
2. **Gestion centralisée (GP)** : la technologie qui permet de gérer les clients et serveurs Windows d'une entreprise ou d'une organisation.

### Avantages de la stratégie de groupe

- **Application en masse** : de nombreux paramètres Windows s'appliquent de façon centralisée en une seule fois.
- **Application forcée** : les paramètres qui ne doivent jamais changer peuvent être verrouillés depuis le centre, ce qui permet de créer un **standard d'entreprise**.

### Ce que permet la stratégie de groupe

1. **Paramètres de sécurité** : appliquer les paramètres de sécurité de Windows.
2. **Paramètres d'application** : diffuser de façon centralisée les paramètres de Windows et de nombreuses autres applications.
3. **Paramètres réseau** : gérer en masse le pare-feu, la sécurité IP, les lecteurs réseau mappés, etc.
4. **Redirection de dossiers** : regrouper les dossiers de profil utilisateur sur un serveur pour faciliter la sauvegarde et la gestion.

### Trois modes de paramétrage

| Mode | Signification |
| --- | --- |
| Activé (Enabled) | Applique le paramètre. |
| Désactivé (Disabled) | Applique le paramètre en sens inverse (le désactive). |
| Non configuré (Not Configured) | Valeur nulle (Null) : rien n'est décidé. |

---

## 2. Structure et étendue de liaison

### Configuration ordinateur et configuration utilisateur

La stratégie de groupe se divise en deux grands blocs.

| Bloc | Cible | Moment d'application |
| --- | --- | --- |
| Configuration ordinateur | **Comptes d'ordinateur** dans AD | Au **démarrage** de l'ordinateur |
| Configuration utilisateur | **Comptes utilisateur** dans AD | À l'**ouverture de session** de l'utilisateur |

### Étendue de liaison

L'étendue détermine **où une stratégie de groupe est liée et appliquée**.

- **Domaine** : l'étendue de base d'AD.
- **Unité d'organisation (UO)** : une unité qui découpe une étendue à l'intérieur du domaine.
- **Site** : permet à la stratégie de suivre l'organisation du réseau.

> On **ne peut pas lier directement** une stratégie à un seul utilisateur, groupe ou ordinateur. Les stratégies sont liées aux domaines, aux UO et aux sites.

---

## 3. Fonctionnement de la stratégie de groupe

Ce que l'on appelle une « stratégie de groupe (GPO) » se compose en réalité de deux parties.

![What a GPO is made of](../images/ad-gpc-gpt.svg)

1. **Conteneur de stratégie de groupe (GPC)** : stocké **dans AD** ; il relie la stratégie à l'endroit où elle s'applique.
2. **Modèle de stratégie de groupe (GPT)** : contient **les paramètres réels** et est stocké dans le dossier **SYSVOL**, créé à l'installation du DC.

---

## 4. Stratégie de groupe (GPO) vs préférences (GPP)

| Élément | Stratégie de groupe (GPO) | Préférences de stratégie de groupe (GPP) |
| --- | --- | --- |
| Caractère obligatoire | Appliquée de façon stricte. (Strictly Enforced) | Appliquée, mais l'utilisateur peut la modifier. |
| L'utilisateur peut-il modifier ? | Non : le paramètre est grisé dans l'interface. | Oui, mais la valeur voulue est réappliquée périodiquement. |
| Usage | Paramètres à imposer, comme la sécurité et les standards de l'entreprise | Domaines supplémentaires que les GPO ne couvrent pas, ou paramètres simples basés sur l'interface |

En général, les **GPO** sont bien plus efficaces pour les paramètres de sécurité et les standards d'entreprise qui doivent être imposés.

### Moment d'actualisation (Refresh)

En plus du démarrage et de l'ouverture de session, les stratégies sont aussi mises à jour **périodiquement**.

- **Intervalle par défaut** : 90 minutes plus un décalage aléatoire de ±30 minutes, soit une application en **60 à 120 minutes**.
- **Paramètres de sécurité** : réappliqués **toutes les 16 heures** même sans changement.
- **Forcer la mise à jour** : si vous ne voulez pas attendre, appliquez immédiatement avec **`gpupdate`**.

```cmd
gpupdate /force
```

---

## 5. Ordre de traitement et filtrage

### Ordre de traitement

Les stratégies s'appliquent en descendant plusieurs niveaux, et **la valeur appliquée en dernier est le résultat final**.

![Group Policy processing order](../images/ad-gpo-order.svg)

1. Stratégie de groupe locale
2. Site
3. Domaine
4. UO

### Stratégies de compte

Les stratégies de compte, comme la **stratégie de mot de passe**, s'appliquent **uniquement au niveau du domaine**.

> Exemple : si le domaine fixe une longueur minimale de mot de passe de 8 caractères, une UO qui en fixe 3 obtient quand même **8**.

### Appliquer et bloquer l'héritage

On peut éviter les conflits entre stratégies ou définir leur priorité.

- **Appliqué (Enforce)** : une stratégie supérieure (p. ex. au niveau du domaine) est **imposée vers le bas** et les niveaux inférieurs ne peuvent pas la remplacer.
- **Bloquer l'héritage (Block Inheritance)** : une UO enfant **refuse les stratégies venant d'en haut**. (Elle ne peut pas bloquer les stratégies appliquées.)

### Filtrage de sécurité et filtrage WMI

- **Filtrage de sécurité** : ajuster les autorisations pour qu'une stratégie exclue certains groupes ou utilisateurs, ou ne s'applique qu'aux groupes voulus.
- **Filtrage WMI** : appliquer une stratégie selon des **conditions sur l'ordinateur**, comme la version du système d'exploitation.

---

## 6. Vérifier les résultats et modèles d'administration

### Vérifier le résultat

- **Jeu de stratégie résultant (RSoP)** : utilisez Résultats de stratégie de groupe dans la Console de gestion des stratégies de groupe (**GPMC**) ou **`rsop.msc`** pour voir quelles stratégies ont finalement été appliquées.
- **`gpresult`** : s'utilise à l'invite de commandes pour voir quelles stratégies la machine a actuellement reçues.

```cmd
gpresult /r
```

### Modèles d'administration

- **L'endroit où sont regroupés les paramètres de base de Windows**, comme le Panneau de configuration et les composants Windows.
- La plupart des paramètres souhaités se règlent ici.

---

## Résumé

- Les deux raisons d'utiliser AD sont l'**authentification centralisée (SSO)** et la **gestion centralisée (stratégie de groupe)**. La stratégie de groupe **applique les paramètres en masse** et les **impose**.
- Les modes sont **Activé / Désactivé / Non configuré**. **La configuration ordinateur s'applique au démarrage**, **la configuration utilisateur à l'ouverture de session**.
- Les stratégies ne peuvent être liées qu'aux **domaines, UO et sites**, pas directement à des utilisateurs, groupes ou ordinateurs individuels.
- Une GPO se compose du **GPC** (dans AD, informations de liaison) et du **GPT** (dans SYSVOL, paramètres réels). Utilisez les **GPO** pour imposer et les **GPP** pour des valeurs par défaut souples.
- L'actualisation se fait toutes les **90 ± 30 minutes**, la sécurité **toutes les 16 heures**, et **`gpupdate`** applique immédiatement.
- L'ordre est **Local → Site → Domaine → UO**, et la dernière valeur l'emporte. **Les stratégies de compte ne s'appliquent qu'au domaine.** Ajustez l'étendue avec **Enforce**, **Block Inheritance** et le **filtrage de sécurité/WMI**.
- Vérifiez les résultats avec **RSoP (`rsop.msc`, GPMC)** et **`gpresult`**.
