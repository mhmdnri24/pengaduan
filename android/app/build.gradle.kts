import java.util.Properties
import java.io.FileInputStream

plugins {
    id("com.android.application")
    id("kotlin-android")
    id("com.google.gms.google-services")
    // The Flutter Gradle Plugin must be applied after the Android and Kotlin Gradle plugins.
    id("dev.flutter.flutter-gradle-plugin")
}

val keystoreProperties = Properties()
val keystorePropertiesFile = rootProject.file("key.properties")
if (keystorePropertiesFile.exists()) {
    keystoreProperties.load(FileInputStream(keystorePropertiesFile))
}

android {
    namespace = "com.example.pengaduan"
    compileSdk = 36  // Required by Flutter plugins (image_picker, geolocator, etc.)
    ndkVersion = "27.0.12077973"

    compileOptions {
        sourceCompatibility = JavaVersion.VERSION_11
        targetCompatibility = JavaVersion.VERSION_11
    }

    kotlinOptions {
        jvmTarget = JavaVersion.VERSION_11.toString()
    }

    signingConfigs {
        create("release") {
            keyAlias = keystoreProperties["keyAlias"] as String?
            keyPassword = keystoreProperties["keyPassword"] as String?
            storeFile = if (keystoreProperties["storeFile"] != null) {
                rootProject.file(keystoreProperties["storeFile"] as String)
            } else null
            storePassword = keystoreProperties["storePassword"] as String?
        }
    }


    defaultConfig {
        // TODO: Specify your own unique Application ID (https://developer.android.com/studio/build/application-id.html).
    // Use the applicationId that matches android/app/google-services.json
    applicationId = "com.example.pengaduan"
        // You can update the following values to match your application needs.
        // For more information, see: https://flutter.dev/to/review-gradle-config.
        minSdk = flutter.minSdkVersion  // Minimum Android 5.0
        targetSdk = 34  // Target Android 14 for compatibility
        versionCode = flutter.versionCode
        versionName = flutter.versionName
        
        // Tambahkan konfigurasi untuk mengurangi memory usage
        multiDexEnabled = true
    }

    buildTypes {
        release {
            // Use the release signing config if key.properties exists, otherwise use debug
            signingConfig = if (keystorePropertiesFile.exists()) {
                signingConfigs.getByName("release")
            } else {
                signingConfigs.getByName("debug")
            }
        }
    }

    packagingOptions {
        jniLibs {
            useLegacyPackaging = false
            keepDebugSymbols += setOf("*/armeabi-v7a/*.so", "*/arm64-v8a/*.so", "*/x86/*.so", "*/x86_64/*.so")
        }
    }
}

flutter {
    source = "../.."
}

dependencies {
    // Use Firebase BOM to manage versions
    implementation(platform("com.google.firebase:firebase-bom:32.2.0"))
    implementation("com.google.firebase:firebase-messaging")
    // Include analytics to satisfy FirebaseMessaging's optional analytics calls
    implementation("com.google.firebase:firebase-analytics")
    
    // Google Maps
    implementation("com.google.android.gms:play-services-maps:18.2.0")
}
