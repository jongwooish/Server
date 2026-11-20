# Linux ISO Image インストールガイド

[한국어](README.ko.md) | [English](../README.md) | **日本語** | [简体中文](README.zh-CN.md) | [Español](README.es.md) | [Français](README.fr.md)

![Debian](https://img.shields.io/badge/Debian-13-A81D33?logo=debian&logoColor=white) ![jigdo](https://img.shields.io/badge/jigdo--lite-DLBD-555555)

Debian VM で **jigdo** を使って Debian DLBD の ISO イメージを作り、Windows PC にダウンロードする手順です。

> 元になる Debian VM については、先に [01. Debian VM インストールガイド](../../01-Debian-VM-Install/README/README.ja.md) を参照してください。

## 目次

- [1. Jigdo VM を作る](#1-jigdo-vm-を作る)
- [2. ネットワークとハードディスク](#2-ネットワークとハードディスク)
- [3. VM へのログインとネットワーク設定](#3-vm-へのログインとネットワーク設定)
- [4. ディスクのフォーマットとマウント](#4-ディスクのフォーマットとマウント)
- [5. jigdo のインストールとファイルのダウンロード](#5-jigdo-のインストールとファイルのダウンロード)
- [6. ISO ファイルを作る](#6-iso-ファイルを作る)
- [7. Windows PC へ ISO をダウンロード](#7-windows-pc-へ-iso-をダウンロード)
- [まとめ](#まとめ)

---

## 1. Jigdo VM を作る

VMware で既存の Debian Linux を元に **Clone** して、**Jigdo** VM を 1 台作ります。

![Clone a Jigdo VM](../images/01-clone-jigdo.png)

---

## 2. ネットワークとハードディスク

Jigdo VM のネットワーク(VMnet)を **NAT** にします。インターネットからファイルをダウンロードする必要があるためです。

![Network: NAT](../images/02-network-nat.png)

VM に**ハードディスクを追加**します。

![Add Hard Disk](../images/03-add-hard-disk.png)

![Disk type: SCSI](../images/04-disk-type-scsi.png)

**Create a new virtual disk** を選びます。

![Create a new virtual disk](../images/05-create-new-disk.png)

ハードディスクを **200GB** 追加します。写真では 2 番目を選んでいますが、あとでディスクをフォーマットできるように 3 番目を選びます。

![Disk capacity](../images/06-disk-capacity.png)

![Disk file](../images/07-disk-file.png)

---

## 3. VM へのログインとネットワーク設定

VM を起動し、**root** でログインします。

![Start the VM](../images/08-vm-start.png)

すぐに **Vim** をインストールします。

```bash
apt install vim
```

![Install Vim](../images/09-install-vim.png)

VM が **DHCP** で IP を受け取るように設定します。

```bash
vim /etc/network/interfaces
```

![vim /etc/network/interfaces](../images/10-vim-interfaces.png)

```text
auto ens33
iface ens33 inet dhcp
```

![iface ens33 inet dhcp](../images/11-interfaces-dhcp.png)

`:wq` で保存して終了し、ネットワークサービスを再起動します。

```bash
systemctl restart networking
```

![Restart networking](../images/12-restart-networking.png)

---

## 4. ディスクのフォーマットとマウント

`fdisk -l` で **200GB のディスク**があることを確認します。

![fdisk -l](../images/13-fdisk.png)

マウントする前に 200GB のディスクを**フォーマット**します。ディスク名は環境によって違うので、`fdisk -l` で確認した 200GB のディスクを指定します(例:200GB のディスクが `/dev/sda` なら `mkfs.ext4 /dev/sda`)。

```bash
mkfs.ext4 /dev/sdb
```

![mkfs.ext4](../images/14-mkfs.png)

マウント用のディレクトリを作ってマウントします。

```bash
mkdir /jigdo
mount /dev/sdb /jigdo
```

![mkdir and mount](../images/15-mount.png)

正しくマウントされたか確認します。

```bash
df -h
```

![df -h](../images/16-df.png)

---

## 5. jigdo のインストールとファイルのダウンロード

**jigdo-file** をインストールします。

```bash
apt install jigdo-file
```

![Install jigdo-file](../images/17-install-jigdo-file.png)

jigdo ファイルのインターネット上のアドレスを探します。<https://cdimage.debian.org/debian-cd> にアクセスし、**current → amd64 → jigdo-dlbd** と進んで下にスクロールします。

![Index of /debian-cd](../images/18-debian-cd-index.png)

ここから **6 つのファイル**(DLBD-1〜3 の `.jigdo` と `.template`)を Linux でダウンロードします。いまブラウザーのアドレスは `https://cdimage.debian.org/debian-cd/current/amd64/jigdo-dlbd/` で、これに各ファイル名を付けたものがダウンロードのアドレスです。

![jigdo-dlbd file list](../images/19-jigdo-dlbd-list.png)

アドレスが見つかったら jigdo ディレクトリに移動します。

```bash
cd /jigdo
```

![cd /jigdo](../images/20-cd-jigdo.png)

ダウンロードする 6 つのファイルのうち 1 つを右クリックして、**リンクのアドレスをコピー**します。

![Copy link address](../images/21-copy-link.png)

Linux で `wget` と入力し、コピーしたアドレスを貼り付けます。こうして template と jigdo を含めて **6 つすべて**をダウンロードします。

```bash
wget https://cdimage.debian.org/debian-cd/current/amd64/jigdo-dlbd/debian-13.0.0-amd64-DLBD-1.jigdo
```

![wget](../images/22-wget.png)

---

## 6. ISO ファイルを作る

1、2、3 のうち 1 を例にすると、まず 1 番の **jigdo ファイルのリンク**をコピーします。

![Copy the jigdo link](../images/23-copy-jigdo-link.png)

Linux で `jigdo-lite` と入力してから、コピーしたアドレスを貼り付けます。**Files to scan** と出たら何も入力せずに Enter で進めます。

```bash
jigdo-lite https://cdimage.debian.org/debian-cd/current/amd64/jigdo-dlbd/debian-13.0.0-amd64-DLBD-1.jigdo
```

![jigdo-lite](../images/24-jigdo-lite.png)

ミラーサイトの入力を求められたら、`http://mirror.siwoo.org/debian` をそのまま貼り付けて Enter を押します。ダウンロードが始まります(**とても時間がかかります**)。

![Mirror](../images/25-mirror.png)

ダウンロードが完了した様子です。

![Download done](../images/26-download-done.png)

---

## 7. Windows PC へ ISO をダウンロード

Windows の **PowerShell** で ISO ファイルを PC にダウンロードします。まず `ip add` で **ens33 の IP アドレス**を確認します。

```bash
ip add
```

![ip add](../images/27-ip-add.png)

PowerShell で VM の IP とファイルのパスを入れて `scp` を実行します。下のように `Connection refused` と出たら、VM で **SSH を有効に**する必要があるという意味です。

```powershell
scp root@<VM IP>:/jigdo/debian-13.0.0/amd64-DLBD-1.iso C:
```

![scp refused](../images/28-scp-refused.png)

VM に **openssh-server** をインストールします。

```bash
apt-get install openssh-server
```

![Install openssh-server](../images/29-install-openssh.png)

SSH サービスを再起動して設定を反映します。

```bash
systemctl restart ssh
```

![Restart ssh](../images/30-restart-ssh.png)

同じコマンドをもう一度入力すると、下のように表示されます。`yes` と入力して Enter を押し、パスワードには **VM に root でログインするときのパスワード**を入力すると、ダウンロードが始まります。

![scp yes](../images/31-scp-yes.png)

> - ホストキーの警告が出たら、PC で `ssh-keygen -R <VM IP>` を実行して古いキーを削除します。
> - root のパスワードを忘れたら、VM で `passwd` を実行して設定し直します。

それでもだめなら、権限を許可すれば Windows で正常にダウンロードできます(**時間がかかります**)。

```bash
chmod 777 ~/.ssh
```

![chmod 777 ~/.ssh](../images/32-chmod-ssh.png)

> `chmod 777` は実習用 VM だけで使う方法です。実際のサーバーではこのように権限を開けません。

---

## まとめ

- 既存の Debian VM を Clone して **Jigdo VM** を作り、ネットワークは **NAT**、ハードディスクは **200GB** を追加します。
- `/etc/network/interfaces` に **DHCP** を設定し、`systemctl restart networking` で反映します。
- 200GB のディスクを **`mkfs.ext4` でフォーマット**し、**`/jigdo` にマウント**してから `df -h` で確認します。
- `jigdo-file` をインストールし、cdimage.debian.org の **jigdo-dlbd** ファイル 6 つを `wget` でダウンロードします。
- **`jigdo-lite <jigdo のアドレス>`** を実行してミラーを入力すると ISO が作られます(時間がかかります)。
- PC へのダウンロードには **`scp`** を使い、接続が拒否されたら **openssh-server** をインストールします。
