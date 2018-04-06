---
title: Configure Login
permalink: configure/logout/
---

# Configure Logout

## OPTIONAL: Single Log Out (SLO) 
If you want to implement SLO, point your logout button to `/sso/logout/request`.
This endpoint initiates the Single Log Out process with the IDP. This must be 
configured with the IDP (and the IDP must support this feature) if you are 
planning on using it. 
