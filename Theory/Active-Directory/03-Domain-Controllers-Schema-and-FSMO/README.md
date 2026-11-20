# Domain Controllers, Schema and FSMO

[한국어](README/README.ko.md) | **English** | [日本語](README/README.ja.md) | [简体中文](README/README.zh-CN.md) | [Español](README/README.es.md) | [Français](README/README.fr.md)

![Windows Server](https://img.shields.io/badge/Windows_Server-Active_Directory-0078D4?logo=windows&logoColor=white)

A look inside the domain controller (DC) that provides AD. This page covers the schema and object structure, the database, authentication service and Group Policy files that make up a DC, the database partitions, the global catalog, and the forest-wide FSMO roles.

## Contents

- [1. What is a domain controller?](#1-what-is-a-domain-controller)
- [2. Schema and object structure](#2-schema-and-object-structure)
- [3. Components of a DC](#3-components-of-a-dc)
- [4. AD database partitions](#4-ad-database-partitions)
- [5. Global catalog (GC)](#5-global-catalog-gc)
- [6. FSMO roles (operations masters)](#6-fsmo-roles-operations-masters)
- [Summary](#summary)

---

## 1. What is a domain controller?

- The computer that provides everything in AD is called a **DC (Domain Controller)**. The only files you can really see on a DC are the **database**.
- Authentication uses the **Kerberos** protocol, and the service that handles it is called the **KDC service**.
- You should have **two or more** domain controllers.

---

## 2. Schema and object structure

Active Directory is built on a skeleton called the **schema**.

- **Objects and attributes**: AD is made of objects, and each object has attributes.
- **Schema**: the basic skeleton of these objects and attributes — **the design definition of the template** — is called the schema.
- **Same structure everywhere**: **an entire forest uses the same schema**.
- **Beginning and end of AD**: every structure in AD starts and ends with the schema.

---

## 3. Components of a DC

A domain controller is the server that provides AD; it contains the authentication service and the database.

![Inside a domain controller](images/ad-dc-components.svg)

| Component | Details |
| --- | --- |
| AD database file | AD data is stored as `NTDS.dit` under the `C:\Windows\NTDS` folder. |
| Authentication service | Uses the Kerberos protocol; the service that handles it is the **KDC** (Key Distribution Center). |
| Group Policy files | Files for processing Group Policy are stored under the **SYSVOL** folder. |

---

## 4. AD database partitions

The AD database (`NTDS.dit`) is stored in several pieces, and each partition has a different **replication scope**.

| Partition | What it stores | Scope |
| --- | --- | --- |
| Schema | Basic structure definitions of objects and attributes | Entire forest |
| Configuration | Settings that must be identical across the forest | Entire forest |
| Domain | Per-domain data such as users, groups and computers | That domain |
| Application | Space set aside for programs that use AD (e.g. DNS) | Chosen DCs |

---

## 5. Global catalog (GC)

The GC is the server that handles **searches and address-book queries across the whole forest**.

![Global catalog and FSMO roles](images/ad-gc-fsmo.svg)

- It is used to find **accounts in other domains** of the same forest, or to search information across the forest.
- GC queries use port **3268**, not the normal LDAP port 389.
- To avoid unnecessary network traffic, it is recommended to have **at least one GC per site**.

---

## 6. FSMO roles (operations masters)

AD uses **multimaster replication**, but important operations that must never conflict are handled by a **single master**. These roles are called **operations masters (FSMO, Flexible Single Master Operations)**.

### Forest-wide operations masters

Each of these roles is held by **only one DC per forest**.

- **Schema Master**: changes, modifies and extends the schema for the whole forest.
- **Domain Naming Master**: manages the domain structure in the forest. (adding and removing domains)

---

## Summary

- A **DC** is the server that provides AD; Kerberos authentication is handled by the **KDC service**. Keep two or more DCs.
- The **schema** defines objects and attributes, and **the whole forest uses the same schema**.
- The heart of a DC is **`C:\Windows\NTDS\NTDS.dit`** (database), the **KDC** (authentication) and **SYSVOL** (Group Policy files).
- Database partitions are **Schema and Configuration** (whole forest), **Domain** (one domain) and **Application** (e.g. DNS).
- The **GC** handles forest-wide searches on port **3268**; place at least one per site.
- Operations that must not conflict are given to one DC as **FSMO** roles. The forest-wide ones are the **Schema Master** and the **Domain Naming Master**.
