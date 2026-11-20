# Active Directory

[한국어](README/README.ko.md) | **English** | [日本語](README/README.ja.md) | [简体中文](README/README.zh-CN.md) | [Español](README/README.es.md) | [Français](README/README.fr.md)

![Windows Server](https://img.shields.io/badge/Windows_Server-Active_Directory-0078D4?logo=windows&logoColor=white)

Active Directory theory notes. Read them in order, lecture 1 to 7.

| Topic | Description |
| --- | --- |
| [01. Active Directory Fundamentals](01-Active-Directory-Fundamentals/) | SSO and directories, authentication and authorization, encryption, domain vs workgroup |
| [02. AD DS Components and LDAP](02-AD-DS-Components-and-LDAP/) | Kerberos and LDAP, physical and logical components, domains, OUs, DNs, forests |
| [03. Domain Controllers, Schema and FSMO](03-Domain-Controllers-Schema-and-FSMO/) | Inside a DC (NTDS.dit, KDC, SYSVOL), schema, partitions, GC, FSMO |
| [04. Installing a Domain Controller and DNS](04-Installing-a-Domain-Controller-and-DNS/) | DC prerequisites and promotion, SRV records, joining the domain, DC locator, DNS queries |
| [05. DNS Zones, Tools and AGDLP](05-DNS-Zones-Tools-and-AGDLP/) | DNS zones and records, adding a DC (Server Core), ADUC/ADAC, groups, AGDLP |
| [06. Computer Accounts, Replication, Sites and RODC](06-Computer-Accounts-Replication-Sites-and-RODC/) | Computer accounts, redirusr/LDIFDE, replication, sites and site links, RODC |
| [07. Group Policy](07-Group-Policy/) | GPO structure and link scope, GPC/GPT, GPO vs GPP, processing order, filtering, gpresult |
