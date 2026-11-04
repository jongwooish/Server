# VMware Template VM

[한국어](README.ko.md) | [English](../README.md) | [日本語](README.ja.md) | **简体中文** | [Español](README.es.md) | [Français](README.fr.md)

![VMware Workstation Pro](https://img.shields.io/badge/VMware_Workstation_Pro-17-607078?logo=vmware&logoColor=white)
![Debian](https://img.shields.io/badge/Debian-A81D33?logo=debian&logoColor=white)
![Ubuntu](https://img.shields.io/badge/Ubuntu-E95420?logo=ubuntu&logoColor=white)
![Windows Server](https://img.shields.io/badge/Windows_Server-2025-0078D4?logo=windows&logoColor=white)

实验或比赛题目需要很多台 VM 时，每个系统都手动安装太费时间。更好的做法是先认真装好一台 VM，把它做成 **Template VM**，其余的都从它 **Clone(克隆)** 出来。

![从 Template VM 到克隆](../images/template-clone-flow.svg)

## 目录

- [1. 关键术语](#1-关键术语)
- [2. 准备 Template VM](#2-准备-template-vm)
- [3. 从模板克隆](#3-从模板克隆)
- [4. 克隆之后: 让每台克隆唯一](#4-克隆之后-让每台克隆唯一)
- [5. 本文件夹中的脚本](#5-本文件夹中的脚本)
- [6. 故障排查](#6-故障排查)
- [7. 快速检查清单](#7-快速检查清单)
- [参考资料](#参考资料)

---

## 1. 关键术语

| 术语 | 含义 |
| --- | --- |
| **Template VM** | 安装一次后作为基准(父)保存的原始 VM |
| **Snapshot(快照)** | 保存 VM 某一时刻的状态。克隆是从快照创建的 |
| **Linked clone(链接克隆)** | 共享父 VM 的虚拟磁盘、只保存自身变化的克隆 |
| **Full clone(完整克隆)** | 完整复制父 VM 的独立克隆 |
| **Template mode** | 保护父 VM 的 VMware 选项。启用期间，VM 及其快照**无法删除** |

---

## 2. 准备 Template VM

### Step 0. 准备基准 VM

只放入所有克隆都需要的内容，每台机器必须不同的内容一律不放。

| ✅ 放入模板 | ❌ 不要放入模板 |
| --- | --- |
| 系统更新、公共软件包 (`vim`、`ssh`、`curl` … ) | 特定角色的主机名 (FW、DNS、WEB1 …) |
| 常用的 vimrc、SSH 设置、时区 | 静态 IP 地址 |
| VMware Tools / open-vm-tools | 特定服务器的证书、私钥、密码 |
| | 加入域 (Windows) |

需要时可用 [`prepare-template-linux.sh`](../scripts/prepare-template-linux.sh) (Linux) 或 [`Prepare-WindowsTemplate.ps1`](../scripts/Prepare-WindowsTemplate.ps1) (Windows) 清理 VM。两个脚本完成后都会关闭 VM 电源。

> **拍快照前先关闭 VM 电源。** VMware 要求父 VM 处于关机状态才能克隆，而且从关机快照创建的克隆启动时就像全新开机的机器一样干净。

### Step 1. 创建名为 `Template` 的快照

**VM → Snapshot → Snapshot Manager → Take Snapshot**

- 名称: `Template`
- 描述(可选): 已安装的内容、日期等

### Step 2. 启用 Template mode

**VM → Settings → Options → Advanced → 勾选 `Enable Template mode (to be used for cloning)`**

![Enable Template mode](../images/enable-template-mode.png)

**为什么?**
- 启用 Template mode 后，**VM 及其快照无法删除**。
- **链接克隆依赖父 VM 的快照。** 父 VM 或快照一旦消失，所有链接克隆都会损坏，而 Template mode 可以防止这种情况。
- VMware 也建议在创建链接克隆时为父 VM 启用 Template mode。

> 远程虚拟机无法启用 Template mode。

至此 Template VM 准备完成。

---

## 3. 从模板克隆

**右键点击 Template VM → Manage → Clone**

1. **Clone from** → `An existing snapshot` → 选择 `Template`
   (启用 Template mode 后，**不能**从*当前状态(current state)*克隆。因此所有克隆都以同一个快照为基准。)
2. **Clone type** → `Create a linked clone` 或 `Create a full clone`
3. 输入新 VM 的名称(例如 `FW`、`DNS`、`WEB1`)和位置 → **Finish**

| | **Linked clone** | **Full clone** |
| --- | --- | --- |
| 工作方式 | 共享父 VM 的磁盘，只保存变化 | 复制整个磁盘 |
| 创建速度 | **几秒** | 几分钟 (取决于磁盘大小) |
| 磁盘占用 | **非常小** | 与父 VM 相同 |
| 性能 | 略低 | 一般更好 |
| 是否需要父 VM | **需要** — 父 VM/快照消失就会损坏 | 不需要 — 完全独立 |
| 父 VM 的快照 | 使用所选快照 | 无法访问父 VM 的快照 |
| 适用场景 | 实验、比赛练习、大量短期使用的 VM | 长期使用或要搬到其他电脑的 VM |

> 实验和比赛练习通常最适合用 **链接克隆**。但之后千万不要删除或修改 Template VM。

---

## 4. 克隆之后: 让每台克隆唯一

克隆向导会为每台克隆分配**新的 MAC 地址和 UUID**，但其余内容 — VM 名称、主机名、**静态 IP** — 都与父 VM 相同。在接入共享网络**之前**，要在每台克隆中修改。

### Linux (Debian / Ubuntu)

| 项目 | 原因 | 解决 |
| --- | --- | --- |
| Hostname | 所有克隆都沿用模板的名字 | `hostnamectl set-hostname WEB1` + `/etc/hosts` 中 `127.0.1.1` 那一行 |
| IP 地址 | 避免 IP 冲突 | `/etc/network/interfaces` (Debian) 或 `/etc/netplan/` (Ubuntu) |
| machine-id | ID 相同时，DHCP 可能给不同的克隆分配**相同的 IP** | `rm /etc/machine-id && systemd-machine-id-setup` |
| SSH 主机密钥 | 所有克隆共用同一套密钥 | `rm /etc/ssh/ssh_host_* && ssh-keygen -A` |

除 IP 外的以上操作，一条命令完成:

```bash
sudo ./post-clone-linux.sh WEB1
```

→ [`scripts/post-clone-linux.sh`](../scripts/post-clone-linux.sh)

### Windows Server

Windows 克隆会拥有相同的 **SID**，导致 Active Directory(加入域等)出问题。
要在拍 `Template` 快照**之前**，在模板中运行 **Sysprep**。

```powershell
C:\Windows\System32\Sysprep\sysprep.exe /generalize /oobe /shutdown
```

| 选项 | 含义 |
| --- | --- |
| `/generalize` | 删除 SID 等机器特有的信息 |
| `/oobe` | 下次启动时显示初始设置界面 |
| `/shutdown` | 完成后关机 → 然后再拍快照 |

→ [`scripts/Prepare-WindowsTemplate.ps1`](../scripts/Prepare-WindowsTemplate.ps1) 会先检查管理员权限和是否已加入域，再执行上面的命令。

---

## 5. 本文件夹中的脚本

| 脚本 | 运行位置 | 时机 | 作用 |
| --- | --- | --- | --- |
| [`prepare-template-linux.sh`](../scripts/prepare-template-linux.sh) | Linux 模板 | 拍快照前 | 清理 apt 缓存、日志、历史记录、DHCP 租约，清空 machine-id，关机 |
| [`post-clone-linux.sh`](../scripts/post-clone-linux.sh) | 每台 Linux 克隆 | 首次启动 | 新的 hostname、machine-id、SSH 主机密钥 |
| [`Prepare-WindowsTemplate.ps1`](../scripts/Prepare-WindowsTemplate.ps1) | Windows 模板 | 拍快照前 | Sysprep `/generalize /oobe /shutdown` |

---

## 6. 故障排查

| 症状 | 原因 | 解决 |
| --- | --- | --- |
| 两台克隆从 DHCP 获得相同的 IP | machine-id 相同 | 重新生成 machine-id ([第 4 节](#4-克隆之后-让每台克隆唯一)) |
| `WARNING: REMOTE HOST IDENTIFICATION HAS CHANGED` / 所有服务器指纹相同 | 共用 SSH 主机密钥 | 重新生成主机密钥，并在客户端执行 `ssh-keygen -R <host>` |
| `sudo: unable to resolve host` | `/etc/hosts` 中仍是旧名称 | 修改 `127.0.1.1` 那一行 |
| Windows 克隆无法加入域 | SID 相同 | 拍快照前在模板中运行 Sysprep |
| 链接克隆找不到父磁盘，无法启动 | 移动或删除了 Template VM | 模板保持原位，并启用 Template mode |
| “从当前状态克隆”为灰色 | 已启用 Template mode | 从 `Template` 快照克隆 |
| 两台克隆的 MAC 地址相同 | 没用向导，而是手动复制了 VMX | VM → Settings → Network Adapter → Advanced → **Generate** |

---

## 7. 快速检查清单

- [ ] 基准 VM 已安装并更新，未放入任何角色专属配置
- [ ] (Linux) `prepare-template-linux.sh` / (Windows) Sysprep
- [ ] VM 已关机
- [ ] 已创建 `Template` 快照
- [ ] 已勾选 **Enable Template mode**
- [ ] 从 `Template` 快照克隆 → 链接或完整
- [ ] 在每台克隆中修改 hostname、IP、machine-id、SSH 主机密钥

---

## 参考资料

- Broadcom / VMware Docs — [Enable Template Mode for a Parent Virtual Machine](https://docs.vmware.com/en/VMware-Workstation-Pro/17/com.vmware.ws.using.doc/GUID-B544E04E-8EDD-4918-A486-7F3B7100E3C0.html)
- Broadcom / VMware Docs — [Clone a Virtual Machine](https://docs.vmware.com/en/VMware-Workstation-Pro/17/com.vmware.ws.using.doc/GUID-C86E70AD-85A9-4D85-AEBB-135AA5648554.html)
- Broadcom / VMware Docs — [Using Full Clones](https://docs.vmware.com/en/VMware-Workstation-Pro/17/com.vmware.ws.using.doc/GUID-F8AC53B9-0577-4410-9935-B6E332E87554.html)
- Microsoft Learn — [Sysprep (Generalize) a Windows installation](https://learn.microsoft.com/windows-hardware/manufacture/desktop/sysprep--generalize--a-windows-installation)
- Microsoft Learn — [Sysprep command-line options](https://learn.microsoft.com/windows-hardware/manufacture/desktop/sysprep-command-line-options)
- systemd — [machine-id(5)](https://www.mankier.com/5/machine-id), [systemd-machine-id-setup(1)](https://www.mankier.com/1/systemd-machine-id-setup)
