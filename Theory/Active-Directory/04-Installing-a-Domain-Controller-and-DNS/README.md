# Installing a Domain Controller and DNS

[한국어](README/README.ko.md) | **English** | [日本語](README/README.ja.md) | [简体中文](README/README.zh-CN.md) | [Español](README/README.es.md) | [Français](README/README.fr.md)

![Windows Server](https://img.shields.io/badge/Windows_Server-Active_Directory-0078D4?logo=windows&logoColor=white)

How a server becomes a domain controller (adding the role and promoting it), and the DNS that AD absolutely depends on. Covers SRV records, how clients join a domain, how a client finds a DC, name-resolution order and the types of DNS queries.

## Contents

- [1. Preparing to install AD](#1-preparing-to-install-ad)
- [2. Installing a domain controller](#2-installing-a-domain-controller)
- [3. DNS and AD (SRV records)](#3-dns-and-ad-srv-records)
- [4. Joining a client to the domain](#4-joining-a-client-to-the-domain)
- [5. More DNS concepts](#5-more-dns-concepts)
- [Summary](#summary)

---

## 1. Preparing to install AD

The server that provides Active Directory is called a **domain controller (DC)**. A few things must be prepared before a server can become a DC.

- **Old way (before 2012)**: the `DCPROMO` command was used.
- **Current way (2012 and later)**: Server Manager gained stronger remote-management features and the installation changed; you **promote the DC** from Server Manager.

### DC prerequisites

- **Static IP**: a DC must use a **static IP**.
- **DNS setting**: the server installing AD must have **its own IP address** (`127.0.0.1` or its own IP) as its preferred DNS.
  - Nowadays the server changes its preferred DNS to itself automatically during AD installation. (In the past it did not, which caused many problems.)
- **Computer name**: decide the server name in advance. (e.g. `DC01`, `DC02`)

---

## 2. Installing a domain controller

There are two stages: add the role, then **promote** the server to a DC.

1. **Add roles and features**: in the Server Manager dashboard, click **Add roles and features**.
2. **Select the service**: check **Active Directory Domain Services**. The management tools needed for AD are installed with it.
3. **Install the base modules**: click Install and the basic AD modules and management tools are installed.
4. **Promote to DC**: when installation finishes, Server Manager shows a **Promote this server to a domain controller** link; clicking it starts the wizard.
5. **Choose the functional level**: unless you have a reason not to, pick **the highest version**.
6. **GC/DNS**: on the first DC the global catalog and DNS server are required, so they are selected automatically.
7. **DSRM password**: set the password for **Directory Services Restore Mode**, used when something goes wrong with the DC.
8. **Reboot**: check the paths and click Install. When installation finishes the server reboots automatically.

### Promotion options

| Option | When to choose it |
| --- | --- |
| Add a new forest | When creating the domain **for the first time** |
| Add a DC to an existing domain | When a DC already exists and you add **one more** for backup or load balancing |

> **NetBIOS name**: if the domain name is `korea.com`, the NetBIOS name becomes `KOREA`. A NetBIOS name can be **up to 15 characters**.

---

## 3. DNS and AD (SRV records)

AD **cannot exist without DNS.** AD finds every computer through DNS.

### What is DNS?

- It turns computer names into IP addresses.
- **FQDN** (Fully Qualified Domain Name): the full name made of the host name (e.g. `www`) plus the domain name (e.g. `koala.com`) — `www.koala.com`.

### What is an SRV record?

- An **SRV (service) record** is a record whose name starts with `_` (underscore). (e.g. `_ldap._tcp.dc._msdcs.korea.com`)
- SRV records carry information such as: the GC is reached on port **3268**, and Kerberos authentication uses port **88**.
- With several DCs, several SRV records are registered, and **round robin** spreads the load when clients look up a DC.
- When AD is installed, the DC's SRV records are **registered automatically**.

---

## 4. Joining a client to the domain

### Required before joining: DNS

- The client must have **the DC's IP address** as its DNS server, or it cannot find the DC.
- If DNS does not point to the DC, **the domain join (AD join) fails.**

### How to join (GUI)

| Method | Details |
| --- | --- |
| Simple change | Enter only the domain name in System Properties and join. To give a domain account local rights, add it to a group yourself in Computer Management. |
| Network ID | Use the **Network ID** wizard in System Properties. It joins the domain and can give the user account **local administrator rights** on the client PC at the same time. |

### How a client finds a DC

When a client looks for a DC to log on or join the domain, it **always uses DNS**.

![How a client finds a DC](images/ad-dc-locator.svg)

1. **Query DNS**: the client asks its DNS server for the domain's **SRV records**.
2. **Get the DC list**: DNS returns the list of SRV records (the DCs' addresses).
3. **Ping**: the client pings the DCs in the list to see which are alive.
4. **Pick the best DC**: it chooses the DC that answers fastest and tries to authenticate.

---

## 5. More DNS concepts

![Name resolution order and query types](images/ad-name-resolution.svg)

### Name-resolution order

A computer does not ask the DNS server first; it checks in this order:

1. **Local host name** (itself)
2. **hosts file** (`C:\Windows\System32\drivers\etc\hosts`)
3. **Local DNS cache**: IP information previously received from DNS
4. **Query the DNS server**

### Types of DNS queries

| Type | Description | Direction |
| --- | --- | --- |
| Recursive | The client asks the DNS server for the **final answer**. | client ⇄ server |
| Iterative | To get an exact answer, the DNS server **repeatedly asks other servers** — root, TLD, authoritative. | server ⇄ server |

---

## Summary

- A DC needs a **static IP**, **DNS pointing to itself** and a **computer name chosen in advance**. From 2012 on, promotion is done in **Server Manager** instead of `DCPROMO`.
- Installation: **add the AD DS role → promote** (new forest / add to existing domain) → functional level → GC/DNS → **DSRM password** → reboot. NetBIOS names are up to **15 characters**.
- AD **does not work without DNS.** **SRV records** starting with `_` are registered automatically so clients can find DCs, the GC (3268) and Kerberos (88).
- A client must **use the DC as its DNS** to join the domain. The **Network ID** wizard also grants local administrator rights.
- Finding a DC: **SRV query → DC list → ping → fastest DC**.
- Name resolution goes **local host name → hosts file → cache → DNS server**; queries are **recursive** (client↔server) or **iterative** (server↔server).
