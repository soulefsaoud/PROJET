pipeline {
    agent any

    options {
        skipDefaultCheckout(true)
    }

    stages {
        stage('🧹 Clean Workspace') {
            steps {
                deleteDir()
            }
        }

        stage('📥 Checkout') {
            steps {
                checkout([
                    $class: 'GitSCM',
                    branches: [[name: '*/main']],
                    userRemoteConfigs: [[
                        url: 'https://github.com/soulefsaoud/PROJET.git'
                    ]]
                ])
            }
        }

        stage('🔨 Build Docker Image') {
            steps {
                sh 'docker compose build'
            }
        }
    }
}

pipeline {
    agent any

    environment {
        REPO_URL = 'https://github.com/soulefsaoud/PROJET.git'
        DOCKER_IMAGE = 'recette_project:latest'
        PROJECT_NAME = 'recette_project'
    }

         stages {
           stage('📥 Checkout') {
               steps {
                   echo '=== Récupération du code depuis Git ==='
                   git branch: 'main', url: env.REPO_URL
               }
           }

           stage('🔨 Build Docker Image') {
               steps {
                   echo '=== Construction de l\'image Docker ==='
                   sh 'docker compose build'
               }
           }

           stage('🚀 Start Services') {
               steps {
                   echo '=== Démarrage des services Docker ==='
                   sh '''
                       docker compose up -d
                       sleep 10
                       docker compose ps
                   '''
               }
           }

           stage('🧪 Run PHPUnit Tests') {
               steps {
                   echo '=== Exécution des tests PHPUnit ==='
                   sh '''
                       docker compose exec -T app php bin/phpunit || true
                   '''
               }
           }

           stage('✅ Code Quality - Lint Twig') {
               steps {
                   echo '=== Vérification de la syntaxe Twig ==='
                   sh '''
                       docker compose exec -T app php bin/console lint:twig templates/ || true
                   '''
               }
           }

           stage('✅ Code Quality - Lint YAML') {
               steps {
                   echo '=== Vérification de la syntaxe YAML ==='
                   sh '''
                       docker compose exec -T app php bin/console lint:yaml config/ || true
                   '''
               }
           }

           stage('🗑️ Cleanup') {
               steps {
                   echo '=== Arrêt et nettoyage des conteneurs ==='
                   sh '''
                       docker compose down || true
                   '''
               }
           }

           stage('🚀 Deploy to Production') {
               when {
                   branch 'main'
                   expression { currentBuild.result == null || currentBuild.result == 'SUCCESS' }
               }
               steps {
                   echo '=== ✅ Déploiement en production ==='
                   sh '''
                       docker compose up -d
                       echo "✅ Application recette_project déployée avec succès !"
                       docker compose ps
                   '''
               }
           }
       }

       post {
           always {
               echo '=== Nettoyage final ==='
               sh 'docker compose down -v || true'
           }
           success {
               echo '✅ Pipeline exécutée avec succès !'
           }
           failure {
               echo '❌ Erreur dans la pipeline !'
           }
       }
   }
