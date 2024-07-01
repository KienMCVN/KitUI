# KitUI
## Plugin KitUI For Pocketmine-MP 5.0.0

## Info Plugin
<ul>
  <li>This Plugin Allow Player To Receive Kit</li>
  <li>Admin Can Create More Kit With Command</li>
  <li>Admin Just Can Add And Remove Item In Game Because Item Will Be Encoded In Config</li>
</ul>

## Commands
<ul>
  <li>/kits Or /kit To Open Menu Receive Kit</li>
  <li>/givekit 'kit' 'player' To Give Kit For Some One</li>
  <li>/createkit 'name' To Create A New Kit</li>
  <li>/managekit 'kit' To Open Menu Manage Kit</li>
</ul>

## Permissions
<ul>
  <li>kit.menu To Use /kits Or /kit</li>
  <li>kit.give To Use /givekit</li>
  <li>kit.create To Use /createkit</li>
  <li>kit.manage To Use /managekit</li>
</ul>

## Config
<details>
  <summary>Click To See</summary>

```yaml
---
economy:
  provider: economyapi
#Get Type Of The Economy

kits:
  - name: "Kit Member"
    info: "Kit For New Member"
    permission: "memberkit.perm"
    money: 1000
    coin: 10
    items: []
    used-by: ""
...
```
</details>
