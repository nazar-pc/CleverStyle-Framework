### [Nightly builds](https://sourceforge.net/projects/cleverstyle-cms/files/nightly/)
Nightly builds represent bleeding edge state of system and components, is updated after each commit, recommended for developers.

### [Stable builds](https://sourceforge.net/projects/cleverstyle-cms/files/stable/)
Stable builds are based on releases, they are more stable, but always much older that nightly builds, recommended for production environments.

Please, read [how to install](/docs/Installation.md) it properly depending on your environment

### Digital signature
All new builds starting from version `2.28.0+build-1076` are digitally signed.
To check signature for `CleverStyle_CMS_2.28.0+build-1076_Core.phar.php` file, download this file and `CleverStyle_CMS_2.28.0+build-1076_Core.phar.php.asc` (same name with `.asc` a the end) and in directory with downloaded files run:
```bash
gpg --recv-keys 0xdeab7b5a526617c0
gpg --verify CleverStyle_CMS_2.28.0+build-1076_Core.phar.php.asc
```
You should get output like this:
```
gpg: assuming signed data in `CleverStyle_CMS_2.28.0+build-1076_Core.phar.php'
gpg: Signature made śro, 29 kwi 2015, 01:15:31 CEST using RSA key ID 526617C0
gpg: Good signature from "Nazar Mokrynskyi (For signing releases of CleverStyle CMS and components) <nazar@mokrynskyi.com>"
gpg: WARNING: This key is not certified with a trusted signature!
gpg:          There is no indication that the signature belongs to the owner.
Primary key fingerprint: 2DF6 0DF6 BBEF 58A3 93B7  6CE6 DEAB 7B5A 5266 17C0
```

Also Git tags starting from `2.28.0+build-1076` are digitally signed, to check signature for tag `2.28.0+build-1076` run:
```bash
gpg --recv-keys 0x8cf6d73db34aafea
git tag -v 2.28.0+build-1076
```

Please, note, that keys for build and tags are different!
