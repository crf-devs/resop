@forecast
Feature:
    In order to forecast my missions,
    As an organization,
    I must be able to search for available users and assets.

    Scenario: As anonymous, I cannot access to the forecast page
        When I go to "/organizations/forecast"
        Then I should be on "/organizations/login"

    Scenario: As an authenticated children organization, I cannot use the forecast search form
        Given I am authenticated as "UL 01-02"
        When I go to "/organizations/forecast"
        Then the response status code should be 403

    Scenario: As an authenticated parent organization, I can access the forecast search form
        Given I am authenticated as "DT75"
        When I go to "/organizations"
        Then I should see "Projections"
        When I follow "Projections"
        Then I should be on "/organizations/forecast"
        And the response status code should be 200
        And I should see "Choisissez une plage horaire pour calculer les équipages possibles."

    @javascript
    Scenario: As an authenticated parent organization, I can use the forecast search form
        Given I am authenticated as "DT75"
        And I am on "/organizations/forecast"
        When I click on "#availableRange"
        Then the ".daterangepicker" should be visible
        When I click on ".daterangepicker .left table tbody td[data-title=r1c0]"
        And I click on ".daterangepicker .left table tbody td[data-title=r1c6]"
        And I press "Valider"
        Then the ".daterangepicker" should not be visible
        When I press "Calculer"
        Then I should be on "/organizations/forecast"
        And I should not see "Choisissez une plage horaire pour calculer les équipages possibles."
        And I should see "Mission type DT75 1"
        And I should see "Mission type DT75 2"
        And I check "Compter aussi les ressources déjà engagées"
        And I press "Calculer"
        Then I should see "Attention: certains bénévoles ou véhicules sont déjà engagés sur d'autres missions."
