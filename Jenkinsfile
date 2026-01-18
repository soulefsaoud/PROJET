pipeline {
   agent {
       docker {
           image 'docker/compose:latest'  // Image avec compose préinstallé
           args '-v /var/run/docker.sock:/var/run/docker.sock'
       }
   }

    environment {
        DOCKER_COMPOSE_CMD = 'docker-compose'
    }

    stages {
        stage('Installation Docker Compose') {
            steps {
                script {
                    echo '=== Installation de Docker Compose ==='
                    sh '''
                        # Installer Docker Compose v2 si pas déjà présent
                        if ! command -v docker-compose &> /dev/null; then
                            apk add --no-cache docker-compose
                        fi

                        # Vérifier la version installée
                        docker-compose --version || docker compose version || echo "Docker Compose non disponible"
                    '''
                }
            }
        }

        stage('Checkout') {
            steps {
                echo '=== Récupération du code source ==='
                git branch: 'main', url: 'https://github.com/soulefsaoud/PROJET.git'
            }
        }

        stage('Vérification') {
            steps {
                script {
                    echo '=== Vérification des fichiers ==='
                    sh '''
                        ls -la
                        if [ -f "docker-compose.yml" ]; then
                            echo "✓ docker-compose.yml trouvé"
                            cat docker-compose.yml
                        elif [ -f "compose.yml" ]; then
                            echo "✓ compose.yml trouvé"
                            cat compose.yml
                        else
                            echo "✗ Aucun fichier compose trouvé!"
                            exit 1
                        fi
                    '''
                }
            }
        }

        stage('Nettoyage') {
            steps {
                echo '=== Nettoyage des conteneurs existants ==='
                sh '''
                    docker-compose down -v --remove-orphans 2>/dev/null || true
                    docker system prune -f || true
                '''
            }
        }

        stage('Build') {
            steps {
                echo '=== Construction des images Docker ==='
                sh '''
                    docker-compose build --no-cache --pull
                '''
            }
        }

        stage('Run') {
            steps {
                echo '=== Démarrage des services ==='
                sh '''
                    docker-compose up -d
                    sleep 5
                    docker-compose ps
                    docker ps
                '''
            }
        }

        stage('Vérification Santé') {
            steps {
                echo '=== Vérification de l\'état des services ==='
                sh '''
                    docker-compose logs --tail=50

                    # Vérifier que les conteneurs sont bien en cours d'exécution
                    if [ $(docker-compose ps | grep -c "Up") -eq 0 ]; then
                        echo "⚠️ Aucun conteneur en cours d'exécution!"
                        docker-compose logs
                        exit 1
                    else
                        echo "✓ Services démarrés avec succès"
                    fi
                '''
            }
        }
    }

    post {
        success {
            echo '=== ✓ Pipeline exécuté avec succès ==='
        }

        failure {
            echo '=== ✗ Échec du pipeline ==='
            sh '''
                echo "=== Logs des conteneurs ==="
                docker-compose logs --tail=100 || true

                echo "=== État des conteneurs ==="
                docker ps -a || true

                echo "=== Images disponibles ==="
                docker images || true
            '''
        }

        always {
            echo '=== Nettoyage final ==='
            sh '''
                docker-compose down -v --remove-orphans 2>/dev/null || true
                docker system prune -f || true
            '''
        }
    }
}
