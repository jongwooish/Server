# VMware Template VM

[한국어](../i18n/ko/VMware/Template-VM.md) | **English** | [日本語](../i18n/ja/VMware/Template-VM.md) | [简体中文](../i18n/zh-CN/VMware/Template-VM.md) | [Español](../i18n/es/VMware/Template-VM.md) | [Français](../i18n/fr/VMware/Template-VM.md)

When a lab or competition task needs many VMs, installing every OS by hand takes far too long. Instead, install one VM properly, turn it into a **Template VM**, and **clone** the rest from it.

This page covers how to prepare the Template VM in VMware Workstation, how to clone from it, and what to fix inside each clone.

---

## 1. What is a Template VM?

A Template VM is the **base (parent) VM** that every clone is created from.

| Term | Meaning |
| --- | --- |
| **Template VM** | The original VM you installed once and keep as the base |
| **Snapshot** | A saved point-in-time state of the VM. Clones are made from a snapshot |
| **Clone** | A new VM copied from the template's snapshot |
| **Template mode** | A VMware option that protects the template so it isn't accidentally deleted or broken |

---

## 2. Prepare the Template VM

### Step 0. Get the base VM ready

Before taking the snapshot, put everything every clone should share into this VM.

- Install the OS and the packages you always use (`vim`, `ssh`, `curl` … )
- Apply your usual base settings (vimrc, SSH settings, etc.)
- **Do not** put anything machine-specific in it: hostname, static IP, certificates, keys
- **Shut the VM down** (Power Off / Shut Down Guest)

> Take the snapshot while the VM is **powered off**. Clones made from a powered-off snapshot boot cleanly, like a freshly started machine.

### Step 1. Create a snapshot named `Template`

**VM → Snapshot → Snapshot Manager → Take Snapshot**

- Name: `Template`
- Description (optional): what is installed, the date, etc.

### Step 2. Enable Template mode

**VM → Settings → Options → Advanced → check `Enable Template mode (to be used for cloning)`**

![Enable Template mode](images/enable-template-mode.png)

**Why do this?**
- The snapshot is kept and **can't be deleted by mistake**.
- The template VM itself is **protected from deletion**.
- This matters because **linked clones depend on the template's snapshot**. If the template or its snapshot disappears, every linked clone breaks.

Now the Template VM is ready, and cloning becomes quick and safe.

---

## 3. Clone from the Template

**Right-click the Template VM → Manage → Clone**

1. **Clone from** → `An existing snapshot` → select `Template`
2. **Clone type** → choose one of the two below
3. Enter the new VM name (e.g. `FW`, `DNS`, `WEB1`) and location → **Finish**

| | **Linked clone** | **Full clone** |
| --- | --- | --- |
| How it works | Shares the template's disk, stores only changes | Copies the whole disk |
| Creation speed | **Seconds** | Minutes (depends on disk size) |
| Disk usage | **Very small** | Same as the template |
| Needs the template? | **Yes** — breaks if the template/snapshot is gone | No — fully independent |
| Good for | Labs, competition practice, many short-lived VMs | VMs you'll keep long-term or move to another PC |

> For labs and competition practice, **linked clones** are usually the best choice: fast and light. Just never delete or modify the Template VM afterwards.

---

## 4. After cloning: fix what must be unique

A clone is an exact copy, so some values are **identical** to the template and to other clones. Fix them in each clone before use.

### Linux (Debian / Ubuntu)

| Item | Why | Command |
| --- | --- | --- |
| Hostname | Every clone has the template's name | `hostnamectl set-hostname WEB1` and edit the `127.0.1.1` line in `/etc/hosts` |
| IP address | Avoid IP conflicts | Edit `/etc/network/interfaces` (Debian) or Netplan (Ubuntu) |
| machine-id | Same ID → DHCP may hand out the **same IP** to different clones | `rm /etc/machine-id && systemd-machine-id-setup` |
| SSH host keys | All clones share the same keys | `rm /etc/ssh/ssh_host_* && dpkg-reconfigure openssh-server` |

```bash
# Example: run inside a new clone, then reboot
hostnamectl set-hostname WEB1
sed -i 's/^127.0.1.1.*/127.0.1.1\tWEB1/' /etc/hosts
rm -f /etc/machine-id && systemd-machine-id-setup
rm -f /etc/ssh/ssh_host_* && dpkg-reconfigure openssh-server
reboot
```

### Windows Server

Windows clones share the same **SID**, which causes problems with Active Directory (domain join, etc.).
Run **Sysprep** in the template **before** taking the `Template` snapshot.

```powershell
C:\Windows\System32\Sysprep\sysprep.exe /generalize /oobe /shutdown
```

- `/generalize` : removes machine-specific info such as the SID
- `/oobe` : starts the first-boot setup screen on the next boot
- `/shutdown` : powers off when done → then take the snapshot

### Network adapter (MAC address)

VMware normally gives each clone a **new MAC address** automatically.
If two clones still show the same MAC, go to **VM → Settings → Network Adapter → Advanced → Generate**.

---

## 5. Summary

1. Install the base VM → shut it down
2. Snapshot Manager → create a snapshot named `Template`
3. Settings → Options → Advanced → **Enable Template mode**
4. Manage → Clone → from the `Template` snapshot → **Linked clone** (fast) or **Full clone** (independent)
5. In each clone: change **hostname, IP, machine-id, SSH host keys** (Windows: **Sysprep** before the snapshot)
