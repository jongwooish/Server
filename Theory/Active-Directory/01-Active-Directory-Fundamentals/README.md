# Active Directory Fundamentals

[한국어](README/README.ko.md) | **English** | [日本語](README/README.ja.md) | [简体中文](README/README.zh-CN.md) | [Español](README/README.es.md) | [Français](README/README.fr.md)

![Windows Server](https://img.shields.io/badge/Windows_Server-Active_Directory-0078D4?logo=windows&logoColor=white)

Why use Active Directory (AD) at all? This page covers SSO and what "directory" means, authentication and authorization, the encryption methods you need to understand AD traffic, and the difference between a workgroup and a domain.

## Contents

- [1. The core goal of AD](#1-the-core-goal-of-ad)
- [2. Security basics: authentication and authorization](#2-security-basics-authentication-and-authorization)
- [3. Protecting data: encryption](#3-protecting-data-encryption)
- [4. Domain vs workgroup](#4-domain-vs-workgroup)
- [5. An analogy](#5-an-analogy)
- [Summary](#summary)

---

## 1. The core goal of AD

### Single Sign-On (SSO)

**The biggest reason to use AD** is to build a **Single Sign-On (SSO)** environment.

- **Without AD (workgroup)**: with 10 computers, you create the account **separately** on all 10, and every password change is also done 10 times.
- **With AD (domain)**: a central server, the **domain controller (DC)**, handles authentication. One account can be **designed to reach every piece of infrastructure in the company**.
- **What AD is for**: **account management**, **authentication** (proving who you are) and **authorization** (what you may do), all done **centrally** in one place.

![Workgroup vs Domain](images/ad-workgroup-vs-domain.svg)

### Why a "directory"?

- **Directory**: not a file-system "folder" but something like a **phone book** — a **store that holds many kinds of information** used in the company, such as accounts and groups.
- **Why "Active"**: unlike a printed phone book that is hard to change, information can be **created, edited, changed and deleted easily whenever you want**, so it is called a **"living phone book"**.
- **Before AD**: up to Windows NT 4.0, account information was kept in the **SAM** (Security Account Manager) database.

---

## 2. Security basics: authentication and authorization

These two ideas are essential for understanding identity checks and access control in AD.

### Authentication

- **Definition**: "proving who I am".
- **How it works**: when a user types an account name and password, the request is sent to the **domain controller (DC)**; if it matches what the DC stores, the DC **confirms** that this is the right user.

### Authorization

- **Definition**: deciding **who can and cannot do something**.
- There are two kinds of authorization: **rights** and **permissions**.

| Item | Right | Permission |
| --- | --- | --- |
| Definition | Allowed to **do** something | Allowed to **use** something |
| Examples | Shut down the computer, access it from the network, connect with Remote Desktop | Read a folder, write a file |
| Key point | Administrator holds every right, so it is called a super account. | Only the **owner** of the object can grant it. |
| Ownership | To change permissions on someone else's object you need the separate **take ownership** right. | — |

---

## 3. Protecting data: encryption

These are the ways data is protected in network communication.

![Symmetric, asymmetric and hybrid encryption](images/ad-encryption.svg)

### Symmetric-key encryption

- **Feature**: the **same key encrypts and decrypts**.
- **Pro**: simple and fast.
- **Con**: the key has to be **delivered safely** to whoever receives the encrypted data.

### Asymmetric-key encryption

- **Feature**: there are **two keys** (asymmetric).
  - **Public key**: used to encrypt and shared with others.
  - **Private key**: used to decrypt and **never shared**.
- **How it works**: data encrypted with the other party's **public key** can be decrypted only with that party's **private key**.
- **Built on it**: certificates and certificate authorities — **PKI** (Public Key Infrastructure).
- **Pro**: much stronger security.

### Digital signature

- **Purpose**: proves that the data **really came from that sender** (identity).
- **How it works**: the sender signs with their **private key**, and the receiver verifies with the sender's **public key**.

### Hybrid encryption (mixed, HTTPS)

- Most encryption solutions use a hybrid that combines the **security** of asymmetric keys with the **speed** of symmetric keys.
- **Principle**: the all-important **symmetric key** itself is sent encrypted with the safe **asymmetric** method, and **all bulk data afterwards** travels with the fast, efficient **symmetric key**.

---

## 4. Domain vs workgroup

In Windows, the two are distinguished by **who manages authentication**.

| Item | Workgroup | Domain |
| --- | --- | --- |
| Default | The default when Windows is installed. | Set up by configuring Active Directory. |
| Who authenticates | Each computer keeps its own accounts and passwords locally. (local authentication) | A central system, the domain controller (DC), handles authentication. |
| AD connection | Not connected to AD. | Connecting to a domain is called "joining the domain". |
| Pros | Simple and managed per machine — fine for small environments. | SSO; security, authentication and authorization are all managed in one place. |
| Cons | You enter an account and password on every computer. | If the central system (DC) breaks, the whole authentication system stops. |

> The key difference is **who performs authentication of the identity**.

---

## 5. An analogy

Think of an AD environment as a **central library system**.

- Register once at the central library (**DC**) (**create an account**) and a single library card (**SSO**) lets you use every reading room in the company (**computers, servers**).
- Each reading room **asks the central library** whether the card is valid before opening the door.
- If the library closes (**DC failure**) or the card is cancelled (**account deleted**), that person can no longer enter any reading room.

---

## Summary

- The main purpose of AD is **SSO**: account management, authentication and authorization are handled **centrally on the DC**.
- A **directory** is a "phone book" of accounts and groups; **Active** means a "living" phone book you can change any time. Before AD (NT 4.0), **SAM** was used.
- **Authentication** is "who are you"; **authorization** is "what can you do". Authorization splits into **rights** and **permissions**.
- **Symmetric keys** are fast but key delivery is the problem; **asymmetric keys** are secure and underpin **PKI** and **digital signatures**. In practice a **hybrid** of both (HTTPS) is used.
- A **workgroup** uses local authentication; in a **domain** the DC authenticates. Domains are convenient, but if the DC stops so does authentication — so DCs are made redundant.
