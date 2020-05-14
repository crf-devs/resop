Feature:
    In order to update my password,
    As a user,
    I must be able to set my password.

    Scenario: As anonymous, I cannot set a password
        When I go to "/user/password"
        Then I should be on "/login"
        And the response status code should be 200

    Scenario: As an admin of an organization with no password set, I see a warning
        Given I am authenticated as "admin203@resop.com"
        When I go to "/"
        Then I should be on "/"
        And the response status code should be 200
        And I should see "Vous devez renseigner votre mot de passe afin d'administrer votre structure."

    Scenario: As a user, I cannot set my password with empty data
        Given I am authenticated as "admin203@resop.com"
        And I am on "/user/password"
        And I should not see a "input#user_password_currentPassword" element
        When I fill in the following:
            | user_password[plainPassword][first]  |  |
            | user_password[plainPassword][second] |  |
        And I press "Valider"
        Then I should be on "/user/password"
        And the response status code should be 400
        And I should see "Cette valeur ne doit pas être vide." in the "label[for=user_password_plainPassword_first] .form-error-message" element

    Scenario: As a user, I cannot set my password with invalid data
        Given I am authenticated as "admin203@resop.com"
        And I am on "/user/password"
        And I should not see a "input#user_password_currentPassword" element
        When I fill in the following:
            | user_password[plainPassword][first]  | foo |
            | user_password[plainPassword][second] | bar |
        And I press "Valider"
        Then I should be on "/user/password"
        And the response status code should be 400
        And I should see "Cette valeur n'est pas valide." in the "label[for=user_password_plainPassword_first] .form-error-message" element

    Scenario Outline: As a user with a password, I cannot update it with an empty or invalid current one
        Given I am authenticated as "admin201@resop.com"
        And I am on "/user/password"
        When I fill in the following:
            | user_password[currentPassword]       | <value> |
            | user_password[plainPassword][first]  | foo     |
            | user_password[plainPassword][second] | foo     |
        And I press "Valider"
        Then I should be on "/user/password"
        And the response status code should be 400
        And I should see "<message>" in the "label[for=user_password_currentPassword] .form-error-message" element
        Examples:
            | value   | message                        |
            |         | Cette valeur n'est pas valide. |
            | invalid | Cette valeur n'est pas valide. |

    Scenario Outline: As a user with a password, I can update it
        Given I am authenticated as "admin201@resop.com"
        And I am on "/user/password"
        When I fill in the following:
            | user_password[currentPassword]       | covid19 |
            | user_password[plainPassword][first]  | covid20 |
            | user_password[plainPassword][second] | covid20 |
        And I press "Valider"
        Then I should be on "/"
        And the response status code should be 200
        And I should see "Votre mot de passe a été mis à jour avec succès."
        When I follow "Déconnexion"
        And I fill in the following:
            | user_login[identifier] | <login> |
            | user_login[password]   | covid20 |
        And I press "Je me connecte"
        Then I should be on "/"
        And the response status code should be 200
        And I should see "NIVOL : 990001A"
        Examples:
            | login              |
            | admin201@resop.com |
            | 990001A            |

    Scenario Outline: As a user without a password, I can set it
        Given I am authenticated as "admin203@resop.com"
        And I am on "/user/password"
        And I should not see a "input#user_password_currentPassword" element
        When I fill in the following:
            | user_password[plainPassword][first]  | covid20 |
            | user_password[plainPassword][second] | covid20 |
        And I press "Valider"
        Then I should be on "/"
        And the response status code should be 200
        And I should see "Votre mot de passe a été mis à jour avec succès."
        When I follow "Déconnexion"
        And I fill in the following:
            | user_login[identifier] | <login> |
            | user_login[password]   | covid20 |
        And I press "Je me connecte"
        Then I should be on "/"
        And the response status code should be 200
        And I should see "NIVOL : 990002A"
        Examples:
            | login              |
            | admin203@resop.com |
            | 990002A            |
