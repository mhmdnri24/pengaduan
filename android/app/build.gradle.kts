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
    namespace = "id.pengaduan"
    compileSdk = 36  // Required by Flutter plugins (image_picker, geolocator, etc.)


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
    applicationId = "id.pengaduan"
        // You can update the following values to match your application needs.
        // For more information, see: https://flutter.dev/to/review-gradle-config.
        minSdk = flutter.minSdkVersion  // Minimum Android 5.0
        targetSdk = 34  // Target Android 14 for compatibility
        versionCode = flutter.versionCode
        versionName = flutter.versionName
        
        // Tambahkan konfigurasi untuk mengurangi memory usage
        multiDexEnabled = true
        
        // Konfigurasi heap size
        manifestPlaceholders["appName"] = "Lapor Pak Wali"
    }

    buildTypes {
        release {
            // Use the release signing config if key.properties exists, otherwise use debug
            signingConfig = if (keystorePropertiesFile.exists()) {
                signingConfigs.getByName("release")
            } else {
                signingConfigs.getByName("debug")
            }
            
            // Fix for AAB build stripping error
            ndk {
                debugSymbolLevel = "SYMBOL_TABLE"
            }
            
            // Tambah proguard untuk mengurangi size
            isMinifyEnabled = true
            proguardFiles(getDefaultProguardFile("proguard-android.txt"), "proguard-rules.pro")
            
        }
    }
    
    // Tambah konfigurasi dex options
    dexOptions {
        javaMaxHeapSize = "2g"
    }

    packaging {
        jniLibs {
            useLegacyPackaging = false
            // Completely disable debug symbol processing
            pickFirsts += setOf("**/libjsc.so")
            keepDebugSymbols += setOf("**/*.so")
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
    
    // Play Core library for split compatibility and deferred components
    implementation("com.google.android.play:core:1.10.3")
}
