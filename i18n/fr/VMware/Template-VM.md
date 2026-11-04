# VMware Template VM

[한국어](../../ko/VMware/Template-VM.md) | [English](../../../VMware/Template-VM.md) | [日本語](../../ja/VMware/Template-VM.md) | [简体中文](../../zh-CN/VMware/Template-VM.md) | [Español](../../es/VMware/Template-VM.md) | **Français**

Quand un TP ou une épreuve de concours demande beaucoup de VM, installer chaque système à la main prend beaucoup trop de temps. À la place, installez soigneusement une seule VM, transformez-la en **Template VM**, puis **clonez** les autres à partir d'elle.

Cette page explique comment préparer la Template VM dans VMware Workstation, comment cloner à partir d'elle, et ce qu'il faut corriger dans chaque clone.

---

## 1. Qu'est-ce qu'une Template VM ?

Une Template VM est la **VM de base (parente)** à partir de laquelle tous les clones sont créés.

| Terme | Signification |
| --- | --- |
| **Template VM** | La VM d'origine, installée une fois et conservée comme base |
| **Snapshot** | L'état enregistré de la VM à un instant donné. Les clones sont créés à partir d'un snapshot |
| **Clone** | Une nouvelle VM copiée depuis le snapshot du modèle |
| **Template mode** | Une option VMware qui protège le modèle contre une suppression ou une modification accidentelle |

---

## 2. Préparer la Template VM

### Étape 0. Préparer la VM de base

Avant de prendre le snapshot, mettez dans cette VM tout ce que les clones doivent partager.

- Installez le système et les paquets que vous utilisez toujours (`vim`, `ssh`, `curl` … )
- Appliquez vos réglages de base habituels (vimrc, réglages SSH, etc.)
- **N'y mettez rien** de propre à une machine : nom d'hôte, IP fixe, certificats, clés
- **Éteignez la VM** (Power Off / Shut Down Guest)

> Prenez le snapshot avec la VM **éteinte**. Les clones créés à partir d'un snapshot éteint démarrent proprement, comme une machine fraîchement allumée.

### Étape 1. Créer un snapshot nommé `Template`

**VM → Snapshot → Snapshot Manager → Take Snapshot**

- Nom : `Template`
- Description (facultatif) : ce qui est installé, la date, etc.

### Étape 2. Activer le Template mode

**VM → Settings → Options → Advanced → cocher `Enable Template mode (to be used for cloning)`**

![Enable Template mode](../../../VMware/images/enable-template-mode.png)

**Pourquoi ?**
- Le snapshot est conservé et **ne peut pas être supprimé par erreur**.
- La Template VM elle-même est **protégée contre la suppression**.
- C'est essentiel car **les linked clones dépendent du snapshot du modèle**. Si le modèle ou son snapshot disparaît, tous les linked clones sont cassés.

La Template VM est maintenant prête, et le clonage devient rapide et sûr.

---

## 3. Cloner à partir du modèle

**Clic droit sur la Template VM → Manage → Clone**

1. **Clone from** → `An existing snapshot` → sélectionnez `Template`
2. **Clone type** → choisissez l'un des deux types ci-dessous
3. Saisissez le nom de la nouvelle VM (par ex. `FW`, `DNS`, `WEB1`) et son emplacement → **Finish**

| | **Linked clone** | **Full clone** |
| --- | --- | --- |
| Fonctionnement | Partage le disque du modèle et n'enregistre que les modifications | Copie tout le disque |
| Vitesse de création | **Quelques secondes** | Quelques minutes (selon la taille du disque) |
| Espace disque | **Très faible** | Identique au modèle |
| Besoin du modèle ? | **Oui** — cassé si le modèle ou le snapshot disparaît | Non — totalement indépendant |
| Idéal pour | TP, entraînement aux concours, nombreuses VM de courte durée | VM conservées longtemps ou déplacées vers un autre PC |

> Pour les TP et l'entraînement aux concours, les **linked clones** sont généralement le meilleur choix : rapides et légers. Simplement, ne supprimez ni ne modifiez jamais la Template VM ensuite.

---

## 4. Après le clonage : corriger ce qui doit être unique

Un clone est une copie exacte : certaines valeurs sont donc **identiques** à celles du modèle et des autres clones. Corrigez-les dans chaque clone avant de l'utiliser.

### Linux (Debian / Ubuntu)

| Élément | Pourquoi | Commande |
| --- | --- | --- |
| Hostname | Tous les clones portent le nom du modèle | `hostnamectl set-hostname WEB1` puis modifier la ligne `127.0.1.1` de `/etc/hosts` |
| Adresse IP | Éviter les conflits d'IP | Modifier `/etc/network/interfaces` (Debian) ou Netplan (Ubuntu) |
| machine-id | Avec le même ID, le DHCP peut attribuer **la même IP** à des clones différents | `rm /etc/machine-id && systemd-machine-id-setup` |
| Clés d'hôte SSH | Tous les clones partagent les mêmes clés | `rm /etc/ssh/ssh_host_* && dpkg-reconfigure openssh-server` |

```bash
# Exemple : à lancer dans un nouveau clone, puis redémarrer
hostnamectl set-hostname WEB1
sed -i 's/^127.0.1.1.*/127.0.1.1\tWEB1/' /etc/hosts
rm -f /etc/machine-id && systemd-machine-id-setup
rm -f /etc/ssh/ssh_host_* && dpkg-reconfigure openssh-server
reboot
```

### Windows Server

Les clones Windows partagent le même **SID**, ce qui pose problème avec Active Directory (jonction au domaine, etc.).
Lancez **Sysprep** dans le modèle **avant** de prendre le snapshot `Template`.

```powershell
C:\Windows\System32\Sysprep\sysprep.exe /generalize /oobe /shutdown
```

- `/generalize` : supprime les informations propres à la machine, comme le SID
- `/oobe` : affiche l'écran de configuration initiale au prochain démarrage
- `/shutdown` : éteint la machine à la fin → prenez ensuite le snapshot

### Carte réseau (adresse MAC)

VMware attribue normalement une **nouvelle adresse MAC** à chaque clone automatiquement.
Si deux clones ont malgré tout la même MAC, allez dans **VM → Settings → Network Adapter → Advanced → Generate**.

---

## 5. Résumé

1. Installer la VM de base → l'éteindre
2. Snapshot Manager → créer un snapshot nommé `Template`
3. Settings → Options → Advanced → **Enable Template mode**
4. Manage → Clone → depuis le snapshot `Template` → **Linked clone** (rapide) ou **Full clone** (indépendant)
5. Dans chaque clone : changer **hostname, IP, machine-id, clés d'hôte SSH** (Windows : **Sysprep** avant le snapshot)
