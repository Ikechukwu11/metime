To create a signed release APK with NativePHP Mobile, generate an Android keystore using php artisan native:credentials android and then compile the signed binary with php artisan native:package android. [1] (/goto?url=CAESbgHEIkXX7tLA0NTY0UbzwsPOqmfSoiiCj428rXTXPwvoeRRO6VRJhJkLUK1bOdfQwaNdrhfZi4x0E9hN0YaQ6IQfHc0V2VF9j9G7LrOTnzY6vfWRnGKYYaBriODovTLzW0cTB9oxPTlmsYrDNuEh), [2] (/goto?url=CAESewHEIkXXEuUZ8jMF8LhmZzeRQVhMuiz9AxOx38iyBE03L750X_OtvJ6NPjIwaGpPqmSntrP7hsnXMs_RcWQWpLAy11XeUDsO4jfT70--SQQHjTdEjhd7sKlEuIY9Xfrd3ic_R5lUHOPvWOqg_1hWkWwKgzjItO0bEc4fMQ)

1. Generate Your Signing Keystore (First-Time Setup)

If you do not already have a .jks or .keystore file, let NativePHP generate one and save its configuration automatically: [1] (/goto?url=CAESbgHEIkXX7tLA0NTY0UbzwsPOqmfSoiiCj428rXTXPwvoeRRO6VRJhJkLUK1bOdfQwaNdrhfZi4x0E9hN0YaQ6IQfHc0V2VF9j9G7LrOTnzY6vfWRnGKYYaBriODovTLzW0cTB9oxPTlmsYrDNuEh)
bash
php artisan native:credentials android
Use code with caution.
• Follow the prompts. Enter a filename (e.g., my-app.jks), set your keystore and key passwords, and fill in your certificate details.
• Verify .env variables. NativePHP writes your signing credentials directly to your .env file:env
ANDROID*KEYSTORE_FILE="/path/to/my-app.jks"
ANDROID_KEYSTORE_PASSWORD="your-keystore-password"
ANDROID_KEY_ALIAS="your-key-alias"
ANDROID_KEY_PASSWORD="your-key-password"
Use code with caution.
• Set production environment. Ensure APP_ENV=production and a fixed NATIVEPHP_APP_VERSION=1.0.0 (not DEBUG) are set in your .env so NativePHP strips dev dependencies and enables public release eligibility. [1] (/goto?url=CAESeAHEIkXX743p0PWguV72lh59p1JmEK7AYCGtNv_eGqsMJEiAMqQntSWqxeV8BaNra1*-yEcoKZqHoV4wNoBJuIuSEVgMnX8MRoJ-R-VCc_Nb2zPn07FHfPx6ovXwWZZNkC85ES0DuRLHOiFYVkwokhQxpihVlq97Iw), [2] (/goto?url=CAESbgHEIkXX7tLA0NTY0UbzwsPOqmfSoiiCj428rXTXPwvoeRRO6VRJhJkLUK1bOdfQwaNdrhfZi4x0E9hN0YaQ6IQfHc0V2VF9j9G7LrOTnzY6vfWRnGKYYaBriODovTLzW0cTB9oxPTlmsYrDNuEh), [3] (/goto?url=CAESfAHEIkXXKeDoyzzXOFNLxSKMbcPpC-jC1mYF8tW5FBtetYfeUYLEP6ME_6ToGjOEpG5ec0pLYUgK52JGgie9W2gKGO5nH6PCZEAlEj3Rb4vavbkNNyg94z5HGJaG41Ph_3ISXYqfa5QzBf32JjR3yhYPxEMg5tgd8m8Hils)
(Warning: Back up your .jks file and passwords securely. If you lose them, you cannot publish updates to the same app ID on Android.)

2. Build the Signed APK

Once your .env has the keystore variables configured, run the packaging command: [1] (/goto?url=CAESbwHEIkXXqBjv0gPJaNdklhfT0Q_bmD_duNoy47ba4ptodH-yQ-hjrZl2BakS_dHIvmaysZpPlSJ_0X2JA85-V4jF6QgmZTcDLcc608ETAxLGWj8pkWt-Iar02Ek_ocw--GgUyzPt7Bv8AborF6rBNg), [2] (/goto?url=CAESbgHEIkXX7tLA0NTY0UbzwsPOqmfSoiiCj428rXTXPwvoeRRO6VRJhJkLUK1bOdfQwaNdrhfZi4x0E9hN0YaQ6IQfHc0V2VF9j9G7LrOTnzY6vfWRnGKYYaBriODovTLzW0cTB9oxPTlmsYrDNuEh)
bash
php artisan native:package android --build-type=release
Use code with caution.
If you prefer passing the credentials directly via CLI flags instead of .env variables, run: [1] (/goto?url=CAESewHEIkXXEuUZ8jMF8LhmZzeRQVhMuiz9AxOx38iyBE03L750X_OtvJ6NPjIwaGpPqmSntrP7hsnXMs_RcWQWpLAy11XeUDsO4jfT70--SQQHjTdEjhd7sKlEuIY9Xfrd3ic_R5lUHOPvWOqg_1hWkWwKgzjItO0bEc4fMQ)
bash
php artisan native:package android \
 --keystore=/path/to/my-app.jks \
 --keystore-password=your-keystore-password \
 --key-alias=your-key-alias \
 --key-password=your-key-password
Use code with caution.
(Note: If you are uploading to the Google Play Store rather than sideloading an APK directly, change --build-type=release to --build-type=bundle to generate a signed .aab file.) [1] (/goto?url=CAESewHEIkXXEuUZ8jMF8LhmZzeRQVhMuiz9AxOx38iyBE03L750X_OtvJ6NPjIwaGpPqmSntrP7hsnXMs_RcWQWpLAy11XeUDsO4jfT70--SQQHjTdEjhd7sKlEuIY9Xfrd3ic_R5lUHOPvWOqg_1hWkWwKgzjItO0bEc4fMQ), [2] (/goto?url=CAESbwHEIkXXqBjv0gPJaNdklhfT0Q_bmD_duNoy47ba4ptodH-yQ-hjrZl2BakS_dHIvmaysZpPlSJ_0X2JA85-V4jF6QgmZTcDLcc608ETAxLGWj8pkWt-Iar02Ek_ocw--GgUyzPt7Bv8AborF6rBNg)

3. Locate Your Signed APK

When the Gradle build finishes, NativePHP automatically opens the output folder. You can also find the signed binary at: [1] (/goto?url=CAESewHEIkXXEuUZ8jMF8LhmZzeRQVhMuiz9AxOx38iyBE03L750X_OtvJ6NPjIwaGpPqmSntrP7hsnXMs_RcWQWpLAy11XeUDsO4jfT70--SQQHjTdEjhd7sKlEuIY9Xfrd3ic_R5lUHOPvWOqg_1hWkWwKgzjItO0bEc4fMQ), [2] (/goto?url=CAESbgHEIkXX7tLA0NTY0UbzwsPOqmfSoiiCj428rXTXPwvoeRRO6VRJhJkLUK1bOdfQwaNdrhfZi4x0E9hN0YaQ6IQfHc0V2VF9j9G7LrOTnzY6vfWRnGKYYaBriODovTLzW0cTB9oxPTlmsYrDNuEh)
text
nativephp/android/app/build/outputs/apk/release/app-release.apk
Use code with caution.
• Install via ADB. To test the signed APK on a connected Android device, run adb install -r nativephp/android/app/build/outputs/apk/release/app-release.apk. [1] (/goto?url=CAESfAHEIkXXKeDoyzzXOFNLxSKMbcPpC-jC1mYF8tW5FBtetYfeUYLEP6ME_6ToGjOEpG5ec0pLYUgK52JGgie9W2gKGO5nH6PCZEAlEj3Rb4vavbkNNyg94z5HGJaG41Ph_3ISXYqfa5QzBf32JjR3yhYPxEMg5tgd8m8Hils)
If you run into a build error during Gradle compilation or signing, paste the terminal output here and I can help troubleshoot your Android SDK, NDK, or keystore setup.
