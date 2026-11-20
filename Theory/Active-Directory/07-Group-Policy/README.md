# Group Policy

[한국어](README/README.ko.md) | **English** | [日本語](README/README.ja.md) | [简体中文](README/README.zh-CN.md) | [Español](README/README.es.md) | [Français](README/README.fr.md)

![Windows Server](https://img.shields.io/badge/Windows_Server-Active_Directory-0078D4?logo=windows&logoColor=white)

Group Policy — the second big reason to use AD. Covers what Group Policy can do, how it is structured and where it can be linked, GPC and GPT, the difference between GPOs and GPPs, refresh intervals, processing order and filtering, and the tools for checking results.

## Contents

- [1. What is Group Policy?](#1-what-is-group-policy)
- [2. Structure and link scope](#2-structure-and-link-scope)
- [3. How Group Policy works](#3-how-group-policy-works)
- [4. Group Policy (GPO) vs preferences (GPP)](#4-group-policy-gpo-vs-preferences-gpp)
- [5. Processing order and filtering](#5-processing-order-and-filtering)
- [6. Checking results and administrative templates](#6-checking-results-and-administrative-templates)
- [Summary](#summary)

---

## 1. What is Group Policy?

There are two big reasons to use AD, and Group Policy is one of them.

1. **Centralized authentication (SSO)**: one sign-in takes care of everything.
2. **Centralized management (GP)**: the technology for managing Windows clients and servers in a company or organization.

### Benefits of Group Policy

- **Bulk application**: many Windows settings can be applied centrally in one go.
- **Enforcement**: settings that must never change can be locked centrally, which lets you build a **company standard**.

### What Group Policy can do

1. **Security settings**: apply Windows security settings.
2. **Application settings**: push settings for Windows and many other apps centrally.
3. **Network settings**: handle firewall, IP security, mapped network drives and so on in bulk.
4. **Folder redirection**: gather user profile folders on a server to make backup and management easier.

### Three setting modes

| Mode | Meaning |
| --- | --- |
| Enabled | Applies the setting. |
| Disabled | Applies the setting the opposite way (turns it off). |
| Not Configured | A null value — nothing is decided. |

---

## 2. Structure and link scope

### Computer and user configuration

Group Policy is split into two big blocks.

| Block | Target | When it applies |
| --- | --- | --- |
| Computer Configuration | **Computer accounts** in AD | When the computer **starts** |
| User Configuration | **User accounts** in AD | When the user **logs on** |

### Link scope

The scope decides **where a Group Policy is linked and applied**.

- **Domain**: the basic scope in AD.
- **Organizational unit (OU)**: a unit that carves out a scope inside the domain.
- **Site**: lets policy follow the network layout.

> You **cannot link directly** to a single user, group or computer. Policies are linked to domains, OUs and sites.

---

## 3. How Group Policy works

What we call a "Group Policy (GPO)" is really made of two parts.

![What a GPO is made of](images/ad-gpc-gpt.svg)

1. **Group Policy Container (GPC)**: stored **in AD**; it links the policy to where it applies.
2. **Group Policy Template (GPT)**: holds **the actual settings** and is stored in the **SYSVOL** folder created when the DC is installed.

---

## 4. Group Policy (GPO) vs preferences (GPP)

| Item | Group Policy (GPO) | Group Policy Preferences (GPP) |
| --- | --- | --- |
| Enforcement | Strictly enforced. | Applied, but the user can change it. |
| Can users change it? | No — the setting is greyed out in the UI. | Yes, but the intended value is reapplied periodically. |
| Use | Settings that must be enforced, such as security and company standards | Extra areas GPOs can't set, or easy GUI-based settings |

In general, **GPOs** are far more effective for security settings and company standards that must be enforced.

### Refresh timing

Besides startup and logon, policies are also updated **periodically**.

- **Default interval**: 90 minutes plus a random offset of ±30 minutes, so they apply within **60–120 minutes**.
- **Security settings**: reapplied **every 16 hours** even if nothing changed.
- **Force an update**: if you don't want to wait, apply immediately with **`gpupdate`**.

```cmd
gpupdate /force
```

---

## 5. Processing order and filtering

### Processing order

Policies are applied down through several levels, and **the value applied last is the final result**.

![Group Policy processing order](images/ad-gpo-order.svg)

1. Local Group Policy
2. Site
3. Domain
4. OU

### Account policies

Account policies such as the **password policy** apply **only at the domain level**.

> Example: if the domain sets a minimum password length of 8, an OU that sets 3 still gets **8**.

### Enforce and Block Inheritance

You can prevent conflicts between policies or set their priority.

- **Enforce**: a higher policy (e.g. at the domain) is **forced down** so lower levels can't override it.
- **Block Inheritance**: a child OU **refuses policies coming from above**. (It cannot block enforced policies.)

### Security filtering and WMI filtering

- **Security filtering**: adjust permissions so that a policy skips certain groups or users, or applies only to the groups you want.
- **WMI filtering**: apply a policy based on **conditions on the computer**, such as the OS version.

---

## 6. Checking results and administrative templates

### Checking the result

- **Resultant Set of Policy (RSoP)**: use Group Policy Results in the Group Policy Management Console (**GPMC**) or **`rsop.msc`** to see which policies were finally applied.
- **`gpresult`**: used at the command prompt to see which policies the machine currently has.

```cmd
gpresult /r
```

### Administrative templates

- **Where Windows' basic settings are collected**, such as Control Panel and Windows components.
- Most of the settings you want are made here.

---

## Summary

- The two reasons to use AD are **centralized authentication (SSO)** and **centralized management (Group Policy)**. Group Policy **applies settings in bulk** and **enforces** them.
- The modes are **Enabled / Disabled / Not Configured**. **Computer configuration applies at startup**, **user configuration at logon**.
- Policies can be linked only to **domains, OUs and sites**, not directly to individual users, groups or computers.
- A GPO consists of the **GPC** (in AD, link information) and the **GPT** (in SYSVOL, the actual settings). Use **GPOs** for enforcement and **GPPs** for flexible defaults.
- Refresh is **90 ± 30 minutes**, security settings **every 16 hours**, and **`gpupdate`** applies immediately.
- The order is **Local → Site → Domain → OU**, and the last value wins. **Account policies apply only at the domain.** Adjust scope with **Enforce**, **Block Inheritance** and **security/WMI filtering**.
- Check results with **RSoP (`rsop.msc`, GPMC)** and **`gpresult`**.
