# VMware Template VM

[한국어](README.ko.md) | [English](../README.md) | [日本語](README.ja.md) | [简体中文](README.zh-CN.md) | [Español](README.es.md) | **Français**

![VMware Workstation Pro](https://img.shields.io/badge/VMware_Workstation_Pro-17-607078?logo=vmware&logoColor=white)
![Debian](https://img.shields.io/badge/Debian-A81D33?logo=debian&logoColor=white)
![Ubuntu](https://img.shields.io/badge/Ubuntu-E95420?logo=ubuntu&logoColor=white)
![Windows Server](https://img.shields.io/badge/Windows_Server-2025-0078D4?logo=windows&logoColor=white)

Quand un TP ou une épreuve de concours demande beaucoup de VM, installer chaque système à la main prend beaucoup trop de temps. À la place, installez soigneusement une seule VM, transformez-la en **Template VM**, puis **clonez** les autres à partir d'elle.

![De la Template VM aux clones](../images/template-clone-flow.svg)

## Sommaire

- [1. Termes clés](#1-termes-clés)
- [2. Préparer la Template VM](#2-préparer-la-template-vm)
- [3. Cloner à partir du modèle](#3-cloner-à-partir-du-modèle)
- [4. Après le clonage : rendre chaque clone unique](#4-après-le-clonage--rendre-chaque-clone-unique)
- [5. Scripts de ce dossier](#5-scripts-de-ce-dossier)
- [6. Dépannage](#6-dépannage)
- [7. Liste de vérification rapide](#7-liste-de-vérification-rapide)
- [Références](#références)

---

## 1. Termes clés

| Terme | Signification |
| --- | --- |
| **Template VM** | La VM d'origine, installée une fois et conservée comme base (parente) |
| **Snapshot** | L'état enregistré de la VM à un instant donné. Les clones sont créés à partir d'un snapshot |
| **Linked clone** | Un clone qui partage le disque virtuel du parent et n'enregistre que ses propres modifications |
| **Full clone** | Une copie complète et indépendante du parent |
| **Template mode** | Une option VMware qui protège le parent : tant qu'elle est active, la VM et ses snapshots **ne peuvent pas être supprimés** |

---

## 2. Préparer la Template VM

### Étape 0. Préparer la VM de base

Mettez dans cette VM tout ce que les clones doivent partager, et rien de ce qui doit être unique.

| ✅ À mettre dans le modèle | ❌ À garder hors du modèle |
| --- | --- |
| Mises à jour du système, paquets communs (`vim`, `ssh`, `curl` … ) | Nom d'hôte d'un rôle précis (FW, DNS, WEB1 …) |
| Votre vimrc habituel, réglages SSH, fuseau horaire | Adresses IP fixes |
| VMware Tools / open-vm-tools | Certificats, clés privées, mots de passe d'un serveur précis |
| | Jonction au domaine (Windows) |

Si besoin, lancez [`prepare-template-linux.sh`](../scripts/prepare-template-linux.sh) (Linux) ou [`Prepare-WindowsTemplate.ps1`](../scripts/Prepare-WindowsTemplate.ps1) (Windows) pour nettoyer la VM. Les deux éteignent la VM à la fin.

> **Éteignez la VM avant de prendre le snapshot.** VMware exige que le parent soit éteint pour le cloner, et les clones créés à partir d'un snapshot éteint démarrent proprement, comme une machine fraîchement allumée.

### Étape 1. Créer un snapshot nommé `Template`

**VM → Snapshot → Snapshot Manager → Take Snapshot**

- Nom : `Template`
- Description (facultatif) : ce qui est installé, la date, etc.

### Étape 2. Activer le Template mode

**VM → Settings → Options → Advanced → cocher `Enable Template mode (to be used for cloning)`**

![Enable Template mode](../images/enable-template-mode.png)

**Pourquoi ?**
- Avec le template mode actif, **la VM et ses snapshots ne peuvent pas être supprimés**.
- **Les linked clones dépendent du snapshot du parent.** Si le parent ou son snapshot disparaît, tous les linked clones sont cassés ; le template mode l'empêche.
- VMware recommande d'activer le template mode sur le parent chaque fois que vous créez des linked clones.

> Le template mode ne peut pas être activé pour une machine virtuelle distante.

La Template VM est maintenant prête.

---

## 3. Cloner à partir du modèle

**Clic droit sur la Template VM → Manage → Clone**

1. **Clone from** → `An existing snapshot` → sélectionnez `Template`
   (Avec le template mode actif, il est **impossible** de cloner depuis l'*état actuel* : tous les clones partent ainsi du même snapshot.)
2. **Clone type** → `Create a linked clone` ou `Create a full clone`
3. Saisissez le nom de la nouvelle VM (par ex. `FW`, `DNS`, `WEB1`) et son emplacement → **Finish**

| | **Linked clone** | **Full clone** |
| --- | --- | --- |
| Fonctionnement | Partage le disque du parent et n'enregistre que les modifications | Copie tout le disque |
| Vitesse de création | **Quelques secondes** | Plusieurs minutes (selon la taille du disque) |
| Espace disque | **Très faible** | Identique au parent |
| Performances | Légèrement inférieures | Généralement meilleures |
| Besoin du parent ? | **Oui** — cassé si le parent ou le snapshot disparaît | Non — totalement indépendant |
| Snapshots du parent | Utilise le snapshot choisi | Aucun accès aux snapshots du parent |
| Idéal pour | TP, entraînement aux concours, nombreuses VM de courte durée | VM conservées longtemps ou déplacées vers un autre PC |

> Pour les TP et l'entraînement aux concours, les **linked clones** sont généralement le meilleur choix. Simplement, ne supprimez ni ne modifiez jamais la Template VM ensuite.

---

## 4. Après le clonage : rendre chaque clone unique

L'assistant de clonage attribue à chaque clone une **nouvelle adresse MAC et un nouvel UUID**, mais tout le reste — nom de la VM, nom d'hôte, **IP fixe** — est identique au parent. Corrigez-le dans chaque clone **avant** de le brancher sur un réseau partagé.

### Linux (Debian / Ubuntu)

| Élément | Pourquoi | Correction |
| --- | --- | --- |
| Hostname | Tous les clones portent le nom du modèle | `hostnamectl set-hostname WEB1` + la ligne `127.0.1.1` de `/etc/hosts` |
| Adresse IP | Éviter les conflits d'IP | `/etc/network/interfaces` (Debian) ou `/etc/netplan/` (Ubuntu) |
| machine-id | Avec le même ID, le DHCP peut attribuer **la même IP** à des clones différents | `rm /etc/machine-id && systemd-machine-id-setup` |
| Clés d'hôte SSH | Tous les clones partagent les mêmes clés | `rm /etc/ssh/ssh_host_* && ssh-keygen -A` |

Tout ce qui précède (sauf l'IP) en une seule commande :

```bash
sudo ./post-clone-linux.sh WEB1
```

→ [`scripts/post-clone-linux.sh`](../scripts/post-clone-linux.sh)

### Windows Server

Les clones Windows partagent le même **SID**, ce qui casse Active Directory (jonction au domaine, etc.).
Lancez **Sysprep** dans le modèle **avant** de prendre le snapshot `Template`.

```powershell
C:\Windows\System32\Sysprep\sysprep.exe /generalize /oobe /shutdown
```

| Option | Signification |
| --- | --- |
| `/generalize` | Supprime les informations propres à la machine, comme le SID |
| `/oobe` | Affiche l'écran de configuration initiale au prochain démarrage |
| `/shutdown` | Éteint la machine à la fin → prenez ensuite le snapshot |

→ [`scripts/Prepare-WindowsTemplate.ps1`](../scripts/Prepare-WindowsTemplate.ps1) vérifie les droits administrateur et l'appartenance à un domaine, puis lance la commande ci-dessus.

---

## 5. Scripts de ce dossier

| Script | Où | Quand | Rôle |
| --- | --- | --- | --- |
| [`prepare-template-linux.sh`](../scripts/prepare-template-linux.sh) | Modèle Linux | Avant le snapshot | Nettoie le cache apt, les journaux, l'historique et les baux DHCP ; vide machine-id ; éteint |
| [`post-clone-linux.sh`](../scripts/post-clone-linux.sh) | Chaque clone Linux | Premier démarrage | Nouveaux hostname, machine-id et clés d'hôte SSH |
| [`Prepare-WindowsTemplate.ps1`](../scripts/Prepare-WindowsTemplate.ps1) | Modèle Windows | Avant le snapshot | Sysprep `/generalize /oobe /shutdown` |

---

## 6. Dépannage

| Symptôme | Cause | Solution |
| --- | --- | --- |
| Deux clones reçoivent la même IP en DHCP | Même machine-id | Régénérer machine-id ([section 4](#4-après-le-clonage--rendre-chaque-clone-unique)) |
| `WARNING: REMOTE HOST IDENTIFICATION HAS CHANGED` / même empreinte sur tous les serveurs | Clés d'hôte SSH partagées | Régénérer les clés d'hôte, puis `ssh-keygen -R <host>` sur le client |
| `sudo: unable to resolve host` | `/etc/hosts` contient encore l'ancien nom | Corriger la ligne `127.0.1.1` |
| Le clone Windows ne peut pas rejoindre le domaine | Même SID | Lancer Sysprep sur le modèle avant le snapshot |
| Le linked clone ne démarre pas : disque parent introuvable | La Template VM a été déplacée ou supprimée | Laisser le modèle en place et utiliser le template mode |
| « Cloner depuis l'état actuel » est grisé | Le template mode est actif | Cloner depuis le snapshot `Template` |
| Deux clones ont la même adresse MAC | Le VMX a été copié à la main au lieu d'utiliser l'assistant | VM → Settings → Network Adapter → Advanced → **Generate** |

---

## 7. Liste de vérification rapide

- [ ] VM de base installée et à jour, sans rien de propre à un rôle
- [ ] (Linux) `prepare-template-linux.sh` / (Windows) Sysprep
- [ ] VM éteinte
- [ ] Snapshot `Template` créé
- [ ] **Enable Template mode** coché
- [ ] Cloner depuis le snapshot `Template` → linked ou full
- [ ] Dans chaque clone : hostname, IP, machine-id, clés d'hôte SSH

---

## Références

- Broadcom / VMware Docs — [Enable Template Mode for a Parent Virtual Machine](https://docs.vmware.com/en/VMware-Workstation-Pro/17/com.vmware.ws.using.doc/GUID-B544E04E-8EDD-4918-A486-7F3B7100E3C0.html)
- Broadcom / VMware Docs — [Clone a Virtual Machine](https://docs.vmware.com/en/VMware-Workstation-Pro/17/com.vmware.ws.using.doc/GUID-C86E70AD-85A9-4D85-AEBB-135AA5648554.html)
- Broadcom / VMware Docs — [Using Full Clones](https://docs.vmware.com/en/VMware-Workstation-Pro/17/com.vmware.ws.using.doc/GUID-F8AC53B9-0577-4410-9935-B6E332E87554.html)
- Microsoft Learn — [Sysprep (Generalize) a Windows installation](https://learn.microsoft.com/windows-hardware/manufacture/desktop/sysprep--generalize--a-windows-installation)
- Microsoft Learn — [Sysprep command-line options](https://learn.microsoft.com/windows-hardware/manufacture/desktop/sysprep-command-line-options)
- systemd — [machine-id(5)](https://www.mankier.com/5/machine-id), [systemd-machine-id-setup(1)](https://www.mankier.com/1/systemd-machine-id-setup)
