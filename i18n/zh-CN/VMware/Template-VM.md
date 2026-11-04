# VMware Template VM

[한국어](../../ko/VMware/Template-VM.md) | [English](../../../VMware/Template-VM.md) | [日本語](../../ja/VMware/Template-VM.md) | **简体中文** | [Español](../../es/VMware/Template-VM.md) | [Français](../../fr/VMware/Template-VM.md)

实验或比赛题目需要很多台 VM 时，每个系统都手动安装太费时间。更好的做法是先认真装好一台 VM，把它做成 **Template VM**，其余的都从它 **Clone(克隆)** 出来。

本页介绍如何在 VMware Workstation 中准备 Template VM、如何从它克隆，以及克隆后在每台 VM 里需要修改的内容。

---

## 1. 什么是 Template VM?

Template VM 是所有克隆的**基准(父)VM**。

| 术语 | 含义 |
| --- | --- |
| **Template VM** | 安装一次后作为基准保存的原始 VM |
| **Snapshot(快照)** | 保存 VM 某一时刻的状态。克隆是从快照创建的 |
| **Clone(克隆)** | 复制 Template 快照得到的新 VM |
| **Template mode** | VMware 的一个选项，用于保护模板不被误删或破坏 |

---

## 2. 准备 Template VM

### Step 0. 准备基准 VM

拍快照之前，先把所有克隆都需要的内容放进这台 VM。

- 安装操作系统和常用软件包 (`vim`、`ssh`、`curl` … )
- 应用常用的基本设置 (vimrc、SSH 设置等)
- **不要**放入每台机器都应不同的内容: 主机名、静态 IP、证书、密钥
- **关闭 VM 电源** (Power Off / Shut Down Guest)

> 快照要在 VM **关机状态**下拍。从关机快照创建的克隆，启动时就像一台全新开机的机器一样干净。

### Step 1. 创建名为 `Template` 的快照

**VM → Snapshot → Snapshot Manager → Take Snapshot**

- 名称: `Template`
- 描述(可选): 已安装的内容、日期等

### Step 2. 启用 Template mode

**VM → Settings → Options → Advanced → 勾选 `Enable Template mode (to be used for cloning)`**

![Enable Template mode](../../../VMware/images/enable-template-mode.png)

**为什么要这样做?**
- 快照会被保留，**不会被误删**。
- Template VM 本身也会**受到保护，不会被删除**。
- 这一点很重要，因为 **Linked clone 依赖模板的快照**。一旦模板或其快照消失，所有 Linked clone 都会损坏。

至此 Template VM 准备完成，可以快速、安全地进行克隆了。

---

## 3. 从模板克隆

**右键点击 Template VM → Manage → Clone**

1. **Clone from** → `An existing snapshot` → 选择 `Template`
2. **Clone type** → 从下面两种中选一种
3. 输入新 VM 的名称(例如 `FW`、`DNS`、`WEB1`)和位置 → **Finish**

| | **Linked clone(链接克隆)** | **Full clone(完整克隆)** |
| --- | --- | --- |
| 工作方式 | 共享模板的磁盘，只保存变化的部分 | 复制整个磁盘 |
| 创建速度 | **几秒** | 几分钟 (取决于磁盘大小) |
| 磁盘占用 | **非常小** | 与模板相同 |
| 是否需要模板 | **需要** — 模板/快照消失就会损坏 | 不需要 — 完全独立 |
| 适用场景 | 实验、比赛练习、大量短期使用的 VM | 长期使用或要搬到其他电脑的 VM |

> 实验和比赛练习通常最适合用 **Linked clone**: 快速又轻量。但之后千万不要删除或修改 Template VM。

---

## 4. 克隆之后: 修改不能重复的值

克隆是完全相同的副本，所以有些值会与模板及其他克隆**完全一样**。使用前要在每台克隆中修改。

### Linux (Debian / Ubuntu)

| 项目 | 原因 | 命令 |
| --- | --- | --- |
| Hostname | 所有克隆都沿用模板的名字 | `hostnamectl set-hostname WEB1`，并修改 `/etc/hosts` 中 `127.0.1.1` 那一行 |
| IP 地址 | 避免 IP 冲突 | 修改 `/etc/network/interfaces` (Debian) 或 Netplan (Ubuntu) |
| machine-id | ID 相同时，DHCP 可能给不同的克隆分配**相同的 IP** | `rm /etc/machine-id && systemd-machine-id-setup` |
| SSH 主机密钥 | 所有克隆共用同一套密钥 | `rm /etc/ssh/ssh_host_* && dpkg-reconfigure openssh-server` |

```bash
# 示例: 在新克隆中执行后重启
hostnamectl set-hostname WEB1
sed -i 's/^127.0.1.1.*/127.0.1.1\tWEB1/' /etc/hosts
rm -f /etc/machine-id && systemd-machine-id-setup
rm -f /etc/ssh/ssh_host_* && dpkg-reconfigure openssh-server
reboot
```

### Windows Server

Windows 克隆会拥有相同的 **SID**，在 Active Directory(加入域等)中会出问题。
要在拍 `Template` 快照**之前**，在模板中运行 **Sysprep**。

```powershell
C:\Windows\System32\Sysprep\sysprep.exe /generalize /oobe /shutdown
```

- `/generalize` : 删除 SID 等机器特有的信息
- `/oobe` : 下次启动时显示初始设置界面
- `/shutdown` : 完成后关机 → 然后再拍快照

### 网络适配器 (MAC 地址)

VMware 通常会自动为每个克隆分配**新的 MAC 地址**。
如果两个克隆的 MAC 仍然相同，请点击 **VM → Settings → Network Adapter → Advanced → Generate**。

---

## 5. 总结

1. 安装基准 VM → 关机
2. Snapshot Manager → 创建名为 `Template` 的快照
3. Settings → Options → Advanced → **Enable Template mode**
4. Manage → Clone → 从 `Template` 快照 → **Linked clone**(快) 或 **Full clone**(独立)
5. 在每台克隆中修改 **hostname、IP、machine-id、SSH 主机密钥** (Windows: 拍快照前先 **Sysprep**)
