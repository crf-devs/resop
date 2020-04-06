Feature:
    In order to fill in my availabilities
    As a user
    I must be able to create my account

    Scenario: As authenticated user, I cannot create an account
        Given I am authenticated as a user
        When I go to "/user/new"
        Then I should be on "/"

    Scenario: As anonymous, I cannot create an account with invalid data
        Given I am on "/user/new"
        When I fill in the following:
            | user[identificationNumber] | 990001A         |
            | user[firstName]            | John            |
            | user[lastName]             | DOE             |
            | user[emailAddress]         | user1@resop.com |
            | user[phoneNumber]          | 0612345678      |
        And I select "UL 05" from "user[organization]"
        And I check "Maraudeur.se"
        And I press "Valider"
        Then the response status code should not be 400
        And I should be on "/user/new"
        And I should see "Cette valeur est déjà utilisée."

    Scenario: As anonymous, I can create an account with valid data
        Given I am on "/user/new"
        When I fill in the following:
            | user[identificationNumber] | 991000A            |
            | user[firstName]            | John               |
            | user[lastName]             | DOE                |
            | user[emailAddress]         | john.doe@resop.com |
            | user[phoneNumber]          | 0612345678         |
        And I select "UL 05" from "user[organization]"
        And I check "Maraudeur.se"
        And I press "Valider"
        Then I should be on "/"
        And I should see "Votre compte utilisateur a été créé avec succès."
        And I should see "Bienvenue, John DOE"
        And I should see "NIVOL : 991000A"
