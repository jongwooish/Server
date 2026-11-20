# Debian VM インストールガイド

[한국어](README.ko.md) | [English](../README.md) | **日本語** | [简体中文](README.zh-CN.md) | [Español](README.es.md) | [Français](README.fr.md)

![Debian](https://img.shields.io/badge/Debian-12-A81D33?logo=debian&logoColor=white) ![VMware Workstation Pro](https://img.shields.io/badge/VMware_Workstation_Pro-17-607078?logo=vmware&logoColor=white)

VMware Workstation Pro で新しい VM を作成し、テキストモードのインストーラーで Debian をインストールする手順です。この VM は、のちに Clone の元になる **Debian CLI VM** として使います。

## 目次

- [1. VM を作成する](#1-vm-を作成する)
- [2. インストール開始と地域の設定](#2-インストール開始と地域の設定)
- [3. ネットワークとホスト名](#3-ネットワークとホスト名)
- [4. アカウントとパスワード](#4-アカウントとパスワード)
- [5. ディスクのパーティション](#5-ディスクのパーティション)
- [6. ソフトウェアの選択と GRUB](#6-ソフトウェアの選択と-grub)
- [まとめ](#まとめ)

---

## 1. VM を作成する

VMware を起動し、**Ctrl + N** を押して新しい VM を作成します。

![New Virtual Machine Wizard](../images/01-new-vm-wizard.png)

起動に必要な **ISO ファイル**を選択します。

![Select the ISO](../images/02-select-iso.png)

![Browse for the ISO](../images/03-browse-iso.png)

**Next** を押し、次の画面まではデフォルトのまま進めます。

![ISO selected](../images/04-iso-selected.png)

![Customize Hardware](../images/05-ready-customize.png)

**Customize Hardware** で **Network Adapter** を **Host-only** に設定します。

![Network Adapter: Host-only](../images/06-network-host-only.png)

必須ではありませんが、必要なら **USB Controller** と **Sound Card** を削除してもかまいません。

![Remove USB and Sound Card](../images/07-remove-usb-sound.png)

**Finish** を押すと VM が自動で起動します。

![Finish](../images/08-finish.png)

---

## 2. インストール開始と地域の設定

**Ctrl + G** で VM に入力を移し、**矢印キー・スペースキー・Enter** で設定します。

![Boot menu](../images/09-boot-menu.png)

> すばやく設定する方法:**English** で Enter → **other** で Enter → **aaa** で Enter → **kkk** で Enter

![Boot menu](../images/10-boot-menu-language.png)

![Select other](../images/11-select-other.png)

自分の国がある大陸を探して選択します。韓国なので **Asia** を選びます。

![Select Asia](../images/12-select-asia.png)

少し下にスクロールして、North Korea ではなく **Korea, Republic of(South Korea)**を選びます。

![Select Korea](../images/13-select-korea.png)

続くロケールの画面は図のとおりに選択します。

![Locale](../images/14-locale.png)

![Locale default](../images/15-locale-default.png)

---

## 3. ネットワークとホスト名

ネットワーク(DHCP)の設定画面が出たら **キャンセル(Cancel)**を押します。

![Cancel DHCP](../images/16-dhcp-cancel.png)

![Network autoconfiguration failed](../images/17-network-failed.png)

いちばん下の項目を選び、**ネットワークは今は設定しません。**

![Do not configure the network now](../images/18-no-network.png)

ホスト名(管理者名)は、課題用紙に指定があればそのとおりに、なければ何でもかまいません(例:`root`)。

![Hostname](../images/19-hostname.png)

---

## 4. アカウントとパスワード

root のパスワードは、課題用紙に指定があればそのとおりに、なければ好きなパスワードを入力します。**Show Password in Clear** にチェックを入れると、入力したパスワードが正しいか確認できます。

![Root password](../images/20-root-password.png)

確認欄にも同じパスワードを入力します。

![Root password verify](../images/21-root-password-verify.png)

ユーザー名も、課題用紙に指定があればそのとおりに、なければ何でもかまいません(例:`sysop`)。

![Full name](../images/22-full-name.png)

アカウント名も上のユーザー名と同じに入力します。

![Username](../images/23-username.png)

ユーザーのパスワードは上の root パスワードと同じにすると楽です。課題用紙に指定があればそれを使います。ここでも **Show Password in Clear** にチェックを入れて確認します。

![User password](../images/24-user-password.png)

確認欄にも同じパスワードを入力します。

![User password verify](../images/25-user-password-verify.png)

> キャプチャーに写っていた実際のパスワードは `********` で隠しています。

---

## 5. ディスクのパーティション

パーティションの方法を聞く画面が出たら、いちばん上の **Guided - use entire disk** を選びます。

![Guided - use entire disk](../images/26-guided-entire-disk.png)

![Select disk](../images/27-select-disk.png)

![Partitioning scheme](../images/28-partition-scheme.png)

この画面が出たら、いちばん下の **Finish partitioning and write changes to disk** を選びます。

![Finish partitioning](../images/29-finish-partitioning.png)

パーティションの変更とフォーマットの確認画面では、**必ず Yes** を選びます。No を押すと、設定を最初からやり直すことになるかもしれません。

![Write changes: Yes](../images/30-write-changes-yes.png)

---

## 6. ソフトウェアの選択と GRUB

よく使うパッケージの情報を開発者に匿名で送るかどうかを聞く画面です。**No** を選びます。

![Popularity contest: No](../images/31-popularity-no.png)

**最後のチェックボックス(standard system utilities)だけを残し**、ほかはすべてチェックを外します。デスクトップなしの CLI でインストールします。

![Software selection](../images/32-software-selection.png)

「GRUB ブートローダーをプライマリドライブにインストールしますか?」という質問です。**Yes** を選びます。

![Install GRUB: Yes](../images/33-grub-yes.png)

GRUB ブートローダーをどこにインストールするかを聞く画面です。**下にあるディスク(/dev/sda)**を選びます。

![GRUB device](../images/34-grub-device.png)

インストールが終わって再起動するという画面です。**Continue** を押せば Linux のインストールは完了です。お疲れさまでした!

![Installation complete](../images/35-install-complete.png)

---

## まとめ

- 新しい VM は **Ctrl + N** で作成し、ISO を選んでから **Network Adapter を Host-only** に変えます(USB・Sound Card の削除は任意)。
- インストーラーは **Ctrl + G** のあと矢印キー・スペースキー・Enter で操作します。地域は **Asia → Korea** です。
- DHCP の設定は**キャンセル**し、**ネットワークはあとで**設定します。
- ホスト名、ユーザー名、パスワードは**課題用紙の値**を使います。Show Password in Clear で入力を確認します。
- パーティションは **Guided - use entire disk → Finish → Yes** です。
- ソフトウェアは **standard system utilities だけ**を残し、GRUB は **Yes → /dev/sda** にインストールします。
