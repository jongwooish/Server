# VMware Template VM

[한국어](README.ko.md) | [English](../README.md) | **日本語** | [简体中文](README.zh-CN.md) | [Español](README.es.md) | [Français](README.fr.md)

![VMware Workstation Pro](https://img.shields.io/badge/VMware_Workstation_Pro-17-607078?logo=vmware&logoColor=white)
![Debian](https://img.shields.io/badge/Debian-A81D33?logo=debian&logoColor=white)
![Ubuntu](https://img.shields.io/badge/Ubuntu-E95420?logo=ubuntu&logoColor=white)
![Windows Server](https://img.shields.io/badge/Windows_Server-2025-0078D4?logo=windows&logoColor=white)

実習や大会の課題で多くの VM が必要なとき、すべての OS を手作業でインストールしていると時間がかかりすぎます。代わりに VM を1台きちんとインストールして **Template VM** にし、残りはそこから **Clone** して使います。

![Template VM から Clone まで](../images/template-clone-flow.svg)

## 目次

- [1. 主な用語](#1-主な用語)
- [2. Template VM の準備](#2-template-vm-の準備)
- [3. Template から Clone する](#3-template-から-clone-する)
- [4. Clone の後: 各 Clone を一意にする](#4-clone-の後-各-clone-を一意にする)
- [5. このフォルダーのスクリプト](#5-このフォルダーのスクリプト)
- [6. トラブルシューティング](#6-トラブルシューティング)
- [7. クイックチェックリスト](#7-クイックチェックリスト)
- [参考資料](#参考資料)

---

## 1. 主な用語

| 用語 | 意味 |
| --- | --- |
| **Template VM** | 一度インストールして基準(親)として保管する元の VM |
| **Snapshot** | VM のある時点の状態を保存したもの。Clone は Snapshot から作られます |
| **Linked clone** | 親の仮想ディスクを共有し、自分の変更分だけを保存する Clone |
| **Full clone** | 親を丸ごとコピーした独立した Clone |
| **Template mode** | 親を保護する VMware のオプション。有効な間は VM とその Snapshot を**削除できません** |

---

## 2. Template VM の準備

### Step 0. 基準 VM の準備

すべての Clone が共通して持つべきものだけを入れ、機器ごとに異なるべきものは入れません。

| ✅ Template に入れるもの | ❌ Template に入れないもの |
| --- | --- |
| OS のアップデート、共通パッケージ (`vim`、`ssh`、`curl` … ) | 特定の役割のホスト名 (FW、DNS、WEB1 …) |
| 普段使う vimrc、SSH 設定、タイムゾーン | 固定 IP アドレス |
| VMware Tools / open-vm-tools | 特定サーバー用の証明書、秘密鍵、パスワード |
| | ドメイン参加 (Windows) |

必要に応じて [`prepare-template-linux.sh`](../scripts/prepare-template-linux.sh) (Linux) または [`Prepare-WindowsTemplate.ps1`](../scripts/Prepare-WindowsTemplate.ps1) (Windows) で VM を整理します。どちらも完了すると VM の電源を切ります。

> **Snapshot を取る前に VM の電源を切ります。** VMware で Clone するには親の電源が切れている必要があり、電源オフの Snapshot から作った Clone は新しく起動したマシンのようにきれいに起動します。

### Step 1. `Template` という Snapshot を作成

**VM → Snapshot → Snapshot Manager → Take Snapshot**

- 名前: `Template`
- 説明(任意): インストールしたもの、日付など

### Step 2. Template mode を有効にする

**VM → Settings → Options → Advanced → `Enable Template mode (to be used for cloning)` にチェック**

![Enable Template mode](../images/enable-template-mode.png)

**なぜ?**
- Template mode が有効だと、**VM とその Snapshot を削除できません**。
- **Linked clone は親の Snapshot に依存します。** 親や Snapshot が消えるとすべての Linked clone が壊れますが、Template mode がそれを防ぎます。
- VMware も、Linked clone を作るときは親で Template mode を有効にすることを推奨しています。

> リモートの仮想マシンでは Template mode を有効にできません。

これで Template VM の準備は完了です。

---

## 3. Template から Clone する

**Template VM を右クリック → Manage → Clone**

1. **Clone from** → `An existing snapshot` → `Template` を選択
   (Template mode が有効だと、*現在の状態(current state)* から Clone することは**できません**。そのため、すべての Clone が同じ Snapshot を基準に作られます。)
2. **Clone type** → `Create a linked clone` または `Create a full clone`
3. 新しい VM の名前(例: `FW`、`DNS`、`WEB1`)と場所を入力 → **Finish**

| | **Linked clone** | **Full clone** |
| --- | --- | --- |
| 仕組み | 親のディスクを共有し、変更分だけを保存 | ディスク全体をコピー |
| 作成速度 | **数秒** | 数分 (ディスクサイズによる) |
| ディスク使用量 | **とても少ない** | 親と同じ |
| 性能 | やや低い | 一般的に高い |
| 親が必要か | **必要** — 親/Snapshot がなくなると壊れる | 不要 — 完全に独立 |
| 親の Snapshot | 選択した Snapshot を使う | 親の Snapshot にはアクセスできない |
| 向いている用途 | 実習、大会練習、短期間使う多数の VM | 長く使う VM、別の PC に移す VM |

> 実習や大会練習には、たいてい **Linked clone** が最適です。ただし、それ以降 Template VM は絶対に削除・変更しないでください。

---

## 4. Clone の後: 各 Clone を一意にする

Clone ウィザードは Clone ごとに**新しい MAC アドレスと UUID** を割り当てますが、それ以外 — VM 名、ホスト名、**固定 IP** — は親と同じです。共有ネットワークにつなぐ**前に**、各 Clone で直します。

### Linux (Debian / Ubuntu)

| 項目 | 理由 | 対処 |
| --- | --- | --- |
| Hostname | すべての Clone が Template の名前を持つ | `hostnamectl set-hostname WEB1` + `/etc/hosts` の `127.0.1.1` の行 |
| IP アドレス | IP の衝突を防ぐ | `/etc/network/interfaces` (Debian) または `/etc/netplan/` (Ubuntu) |
| machine-id | ID が同じだと、DHCP が別々の Clone に**同じ IP** を払い出すことがある | `rm /etc/machine-id && systemd-machine-id-setup` |
| SSH ホストキー | すべての Clone が同じ鍵を共有する | `rm /etc/ssh/ssh_host_* && ssh-keygen -A` |

IP 以外の上記をコマンド1つで:

```bash
sudo ./post-clone-linux.sh WEB1
```

→ [`scripts/post-clone-linux.sh`](../scripts/post-clone-linux.sh)

### Windows Server

Windows の Clone は同じ **SID** を持つため、Active Directory(ドメイン参加など)が壊れます。
`Template` の Snapshot を取る**前に**、Template の中で **Sysprep** を実行します。

```powershell
C:\Windows\System32\Sysprep\sysprep.exe /generalize /oobe /shutdown
```

| オプション | 意味 |
| --- | --- |
| `/generalize` | SID などの機器固有の情報を削除 |
| `/oobe` | 次回起動時に初期設定画面を表示 |
| `/shutdown` | 完了したら電源を切る → その後 Snapshot を取る |

→ [`scripts/Prepare-WindowsTemplate.ps1`](../scripts/Prepare-WindowsTemplate.ps1) は管理者権限とドメイン参加の有無を確認してから、上のコマンドを実行します。

---

## 5. このフォルダーのスクリプト

| スクリプト | 実行場所 | タイミング | 内容 |
| --- | --- | --- | --- |
| [`prepare-template-linux.sh`](../scripts/prepare-template-linux.sh) | Linux の Template | Snapshot の前 | apt キャッシュ・ログ・履歴・DHCP リースを整理、machine-id を空に、電源オフ |
| [`post-clone-linux.sh`](../scripts/post-clone-linux.sh) | 各 Linux の Clone | 初回起動時 | 新しい hostname、machine-id、SSH ホストキー |
| [`Prepare-WindowsTemplate.ps1`](../scripts/Prepare-WindowsTemplate.ps1) | Windows の Template | Snapshot の前 | Sysprep `/generalize /oobe /shutdown` |

---

## 6. トラブルシューティング

| 症状 | 原因 | 対処 |
| --- | --- | --- |
| 2つの Clone が DHCP で同じ IP を受け取る | machine-id が同じ | machine-id を再生成 ([4章](#4-clone-の後-各-clone-を一意にする)) |
| `WARNING: REMOTE HOST IDENTIFICATION HAS CHANGED` / すべてのサーバーのフィンガープリントが同じ | SSH ホストキーを共有 | ホストキーを再生成し、クライアントで `ssh-keygen -R <host>` |
| `sudo: unable to resolve host` | `/etc/hosts` に古い名前が残っている | `127.0.1.1` の行を修正 |
| Windows の Clone がドメインに参加できない | SID が同じ | Snapshot の前に Template で Sysprep |
| Linked clone が親ディスクを見つけられず起動しない | Template VM を移動または削除した | Template はその場所に置いたまま、Template mode を使う |
| 「現在の状態から Clone」がグレーアウト | Template mode が有効 | `Template` Snapshot から Clone |
| 2つの Clone の MAC アドレスが同じ | ウィザードを使わず VMX を手でコピーした | VM → Settings → Network Adapter → Advanced → **Generate** |

---

## 7. クイックチェックリスト

- [ ] 基準 VM をインストール・更新済み、役割固有の設定は入れていない
- [ ] (Linux) `prepare-template-linux.sh` / (Windows) Sysprep
- [ ] VM の電源オフ
- [ ] `Template` Snapshot を作成
- [ ] **Enable Template mode** にチェック
- [ ] `Template` Snapshot から Clone → Linked または Full
- [ ] 各 Clone で hostname、IP、machine-id、SSH ホストキーを変更

---

## 参考資料

- Broadcom / VMware Docs — [Enable Template Mode for a Parent Virtual Machine](https://docs.vmware.com/en/VMware-Workstation-Pro/17/com.vmware.ws.using.doc/GUID-B544E04E-8EDD-4918-A486-7F3B7100E3C0.html)
- Broadcom / VMware Docs — [Clone a Virtual Machine](https://docs.vmware.com/en/VMware-Workstation-Pro/17/com.vmware.ws.using.doc/GUID-C86E70AD-85A9-4D85-AEBB-135AA5648554.html)
- Broadcom / VMware Docs — [Using Full Clones](https://docs.vmware.com/en/VMware-Workstation-Pro/17/com.vmware.ws.using.doc/GUID-F8AC53B9-0577-4410-9935-B6E332E87554.html)
- Microsoft Learn — [Sysprep (Generalize) a Windows installation](https://learn.microsoft.com/windows-hardware/manufacture/desktop/sysprep--generalize--a-windows-installation)
- Microsoft Learn — [Sysprep command-line options](https://learn.microsoft.com/windows-hardware/manufacture/desktop/sysprep-command-line-options)
- systemd — [machine-id(5)](https://www.mankier.com/5/machine-id), [systemd-machine-id-setup(1)](https://www.mankier.com/1/systemd-machine-id-setup)
