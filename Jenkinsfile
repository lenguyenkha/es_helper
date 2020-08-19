pipeline {
  agent {
    node {
      label 'ec2-fleet'
    }
  }

  options {
    timeout(time: 30, unit: "MINUTES")
    buildDiscarder(
      logRotator(
        numToKeepStr:   env.BRANCH_NAME ==~ /master/ ? '15' :
                        env.BRANCH_NAME ==~ /develop/ ?  '15' : '5'
      )
    )
  }

  environment {
    CUSTOM_BUILD_TAG = "${BUILD_TAG}".replaceAll(~/%2F/,"-")
    WORKSPACE = "${env.WORKSPACE}"
  }

  stages {
    stage('get git tag'){
        steps {
            script {
                env.GIT_TAG = sh(returnStdout: true, script: 'git tag --sort=-creatordate --points-at ${GIT_COMMIT} | head -n 1 || :').trim()
            }
        }
    }
    stage('prepare to deploy') {
      when {
        anyOf { branch 'develop'; branch 'master'; }
      }

      steps {
        withCredentials([file(credentialsId: 'ESE_KEY', variable: 'deploy_key')]) {
          sh 'cp \$deploy_key $(pwd)/.id_rsa'
        }
      }
    }

    stage('deploy to develop') {
      when {
        allOf {
          branch 'develop';
          not {
            expression { env.GIT_TAG =~ /(qa)/ };
          }
        }
      }

      steps {
        sh 'ANSIBLE_HOST_KEY_CHECKING=false ansible-playbook -i playbooks/inventories/ese-dev/hosts playbooks/main.yaml --ssh-common-args=" -F $(pwd)/ssh.cfg -o StrictHostKeyChecking=no" -e "hash=${GIT_COMMIT} WORKSPACE=$(pwd)"'
      }
    }
    stage('deploy qa') {
      when {
          allOf {
              expression { env.GIT_TAG =~ /(qa)/ };
              branch 'develop';
          }
      }
      steps {
          sh 'ANSIBLE_HOST_KEY_CHECKING=false ansible-playbook -i playbooks/inventories/ese-testing/hosts playbooks/main.yaml --ssh-common-args=" -F $(pwd)/ssh.cfg -o StrictHostKeyChecking=no" -e "hash=${GIT_COMMIT} WORKSPACE=$(pwd)"'
      }

    }
  }

  post {
    always {
      echo "Finished"
      
    }
  }
}