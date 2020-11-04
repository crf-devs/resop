@reset_password
Feature:
    In order to reset my password,
    As a user,
    I must be able to request a token and reset my password.

    Scenario: As a user, I cannot request a token
        Given I am authenticated as "admin201@resop.com"
        When I go to "/reset-password"
        Then I should be on "/"
        And the response status code should be 200

    Scenario: As anonymous, I can request a token
        When I go to "/"
        Then I should be on "/login"
        And the response status code should be 200
        And I should see "J'ai oublié mon mot de passe"
        When I follow "J'ai oublié mon mot de passe"
        Then I should be on "/reset-password"
        And the response status code should be 200
        When I fill in "reset_password_request_form[emailAddress]" with "admin201@resop.com"
        And I press "Valider"
        Then I should be on "/reset-password/check-email"
        And I should see "Un email vous a été envoyé contenant un lien vous permettant de réinitialiser mon mot de passe. Ce lien expirera dans 1 heure(s)."
        And 1 mail should be sent
        # TODO Check email content & link
        Then I open mail with subject "J'ai oublié mon mot de passe"
        And I click on the "#reset-password" link in mail
        Then I should be on "/reset-password/reset"
        Then I purge mails

        # As anonymous, I cannot request a token if I already requested one in the configured time
        Given I am on "/reset-password"
        When I fill in "reset_password_request_form[emailAddress]" with "admin201@resop.com"
        And I press "Valider"
        Then I should be on "/reset-password"
        And I should see "Vous avez déjà demandé la réinitialisation de votre mot de passe."
        And 0 mail should be sent

    Scenario: As a user, I cannot reset my password using a valid token
        Given I am authenticated as "admin201@resop.com"
        When I go to the reset password page of "admin201@resop.com"
        Then I should be on "/"
        And the response status code should be 200

    Scenario: As anonymous, I can reset my password using a valid token
        When I go to the reset password page of "admin201@resop.com"
        Then I should be on "/reset-password/reset"
        And the response status code should be 200
        And I should see "Mot de passe"
        And I should see "Confirmation"
        When I fill in the following:
            | change_password_form[plainPassword][first]  | test |
            | change_password_form[plainPassword][second] | test |
        And I press "Réinitialiser mon mot de passe"
        Then I should be on "/login"
        And the response status code should be 200
        And I should see "Votre mot de passe a été mis à jour avec succès."
        When I fill in the following:
            | user_login[identifier] | 990001A |
            | user_login[password]   | test    |
        And I press "Je me connecte"
        Then I should be on "/"
        And the response status code should be 200
        And I should see "NIVOL : 990001A"

    Scenario: As anonymous, I cannot reset my password using a invalid token
        When I go to "/reset-password/reset/invalid"
        Then I should be on "/reset-password"
        And the response status code should be 200
        And I should see "Le lien de réinitialisation est invalide ou a expiré"
