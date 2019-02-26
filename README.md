# Serve
Please run php artisan serve to query postman requests.

# Coffee Drop API
This task requires you to build a JSON API. HTML, CSS and Javascript work will be reviewed but is entirely optional.
 
## Brief
A brand new start up, CoffeeDrop, have spotted a gap in the market to build an Android and IOS mobile app which shows their existing 16 national coffee shops, listing them as "locations" for recycling Nespresso coffee pods, for which the client will recieve "cashback" - money for each pod.
After initial meetings with CoffeeDrop, they have asked us to develop a small API which allows a customer using their mobile app to enter their postcode, and be informed of their nearest (as the crow flies) CoffeeDrop location and their opening times.
The API must also allow CoffeeDrop to add a new recycling center, as well as calculate for the user of the app the total amount of "cashback" they will recieve according to an algorithm listed below.
 
### Endpoints Required (Hint: see postman file)
 1. Accepts a postcode, returns the address and opening times of the closest CoffeeDrop Location
 2. Accepts a postcode and set of opening and closing times, creates a new location which will then show up in the results of endpoint 1
 3. Accepts a quantity of each of the three szies of used coffee pods as raw post data in the format 
 
 {
	"Ristretto":10,
	"Espresso":100,
  	"Lungo":30
  }
  
  and returns the amount in pounds and pence that the client will recieve in cashback according to the following rules:
  
  The first 50 capsules: [Ristretto = 2p, Espresso = 4p, Lungo = 6p]
  
  Capsules 50-500: [Ristetto = 3p, Espresso = 6p Lungo = 9p]
  
  Capsules 501+: [Ristretto = 5p, Espresso = 10p, Lungo = 15p]
  
  These requests should be saved in the database for...
  
  4. (Optional) Returns the last 5 calculations done by 3. as well as their result and data about the person sending the request (for the fake marketing team!)
 
## What we are looking for
 - Use of the Haversine Formula for calculating distances from latitude and longitude
 - Use of the Postcodes.io (http://postcodes.io/) postcode lookup API
 - Use of Laravel Eloquent Resources (https://laravel.com/docs/5.6/eloquent-resources)
 - Use of MVC
 - Use of Eloquent, Requests and Routes
 - IMPORTANT: Database migrations and a seeder to seed the data in the location_data.csv file in this repo
 - Clean, well-commented code 
 - An updated Postman (https://www.getpostman.com/) file (included in this repository) which allows us to query the API
 
## Optional
 - A web or mobile app (?!) app to interract with the API
 - Authentication (We use Laravel Passport at Image+)
 
 
 ## Submission Instructions
  - Please email your contact at Image+ with a link to a fork of this github repository containing your response

