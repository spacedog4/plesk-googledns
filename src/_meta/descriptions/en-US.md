This extension integrates Plesk with Google DNS, so you can:
- Synchronize all DNS zones records between Plesk and Google DNS name servers at once
- Push DNS updates automatically to Google DNS

### How to configure

You need o active Cloud DNS API on Google API Console
- Go to [Google Api Console](https://console.developers.google.com/apis/dashboard)
- Choose your project
- Search for "DNS" and select ["Cloud DNS API"](https://console.developers.google.com/apis/library/dns.googleapis.com)
- Active "Cloud DNS API", you may need to enable billing

If you are using a domain in plesk instead of the IP Address, you must add it to the [authorized domains list](https://console.developers.google.com/apis/credentials/consent)
- Go to [Google Api Console](https://console.developers.google.com/apis/dashboard)
- Choose your project
- On the left menu, go to [OAuth consent screen](https://console.developers.google.com/apis/credentials/consent)
- Choose External
- Scroll to "Authorized domains" and add your domain without any http/https or path. Ex.: my-plesk-domain.com
- Press Enter
- Save it

You have to create a OAuth 2.0 credential on Google API Console
- Go to [Google Api Console](https://console.developers.google.com/apis/dashboard)
- Choose your project
- On the left menu, go to [Credentials](https://console.developers.google.com/apis/credentials)
- Click on "Create Credentials" 
- Click on "OAuth client ID"
- Choose "Web Application"
- Inside "Authorized redirect URIs" add your plesk domain/ip followed by */modules/googledns/index.php/index/authenticate*. Ex.: https://my-plesk-domain.com/modules/googledns/index.php/index/authenticate
- Save "Your Client ID" and "Your Client Secret" to set it on plesk extension later


### Notice
You must create the domain in [Google Console Platform](https://console.cloud.google.com/net-services/dns/zones/) to be able to synchronize it.

I hope to improve this extension and be able to also automatically create the zones, you can help to improve this extension throught the [github repository](https://github.com/spacedogcs/plesk-googledns)
