pipeline {
 agent {
     docker {
         image 'docker:26.1.5-cli'
         args '-v /var/run/docker.sock:/var/run/docker.sock'
     }
 }

    stages {
        stage('Checkout') {
            steps {
                git branch: 'main', url: 'https://github.com/soulefsaoud/PROJET.git'
            }
        }

        stage('Build') {
            steps {
                sh 'docker compose down -v || true'
                sh 'docker compose build'
            }
        }

        stage('Run') {
            steps {
                sh 'docker compose up -d'
                sh 'docker compose ps'
            }
        }
    }

    post {
        always {
            sh 'docker compose down -v || true'
        }
    }
}
