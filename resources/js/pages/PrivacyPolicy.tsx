import React from "react";
import { Link } from 'react-router-dom';

const PrivacyPolicy = () => {
  return (
    <div className="bg-gray-50 py-16">
      <div className="container mx-auto px-4">
        <div className="max-w-4xl mx-auto">
          <div className="mb-8 text-center">
            <h1 className="text-4xl font-bold text-dodo-blue mb-4">Privacy Policy</h1>
            <p className="text-xl font-semibold text-gray-700">Our very own privacy policy</p>
          </div>
          
          <div className="bg-white rounded-lg shadow-lg p-8 text-gray-800">
            <h2 className="text-2xl font-bold mb-4">Privacy Policy Notice</h2>
            <p className="mb-6">
              This privacy policy notice is served by Dodo under this website (www.dodoapp.net).
              The purpose of this policy is to explain to you how we control, process, handle and
              protect your personal information while you use our app or our website.
              If you do not agree to the following policy you may wish to cease
              viewing / using this website, and or refrain from submitting your personal data to
              us.
            </p>
            
            <h3 className="text-xl font-bold mb-3">Policy key definitions</h3>
            <ul className="list-disc pl-6 mb-6 space-y-1">
              <li>"Dodo app" refers to the app which can be found on <a
                href="https://play.google.com/store/apps/details?id=csocsort.hu.machiato32.csocsort_szamla"
                target="_blank" rel="noopener noreferrer" className="text-dodo-blue hover:underline">Google Play</a>, on the <a 
                href="https://apps.apple.com/us/app/lender-finances-for-groups/id1558223634"
                target="_blank" rel="noopener noreferrer" className="text-dodo-blue hover:underline">App Store</a>, on the <a 
                href="https://apps.microsoft.com/store/detail/dodo-secure-bill-splitting/9NVB4CZJDSQ7?hl=en-us&gl=us"
                target="_blank" rel="noopener noreferrer" className="text-dodo-blue hover:underline">Windows Store</a> and on <a 
                href="https://app.dodoapp.net"
                target="_blank" rel="noopener noreferrer" className="text-dodo-blue hover:underline">Web</a></li>
              <li>"our", "us", or "we" refer to the business, the developers and administrators of the Dodo app
                and this website</li>
              <li>"you", "the user" "the users" refer to the person(s) using Dodo app and this website</li>
              <li>"group" refers to the groups which you can create and join in the Dodo app</li>
              <li>"member" refers to the above groups' members</li>
              <li>GDPR means General Data Protection Regulation</li>
              <li>Cookies mean small files stored on a user's computer or device</li>
              <li><a href="https://www.naih.hu" target="_blank" rel="noopener noreferrer" className="text-dodo-blue hover:underline">NAIH</a> means Hungarian National Authority for Data Protection and Freedom of Information</li>
            </ul>
            
            <h3 className="text-xl font-bold mb-3">Key principles of GDPR</h3>
            <p className="mb-6">
              Our privacy policy embodies the following key principles; (a) Lawfulness, fairness
              and transparency, (b) Purpose limitation, (c) Data minimisation, (d) Accuracy, (e)
              Storage limitation, (f) Integrity and confidence, (g) Accountability.
            </p>
            
            <h2 className="text-2xl font-bold mt-8 mb-4">Processing your personal data</h2>
            <p className="mb-6">
              Under the GDPR (General Data Protection Regulation) we control and / or process
              any personal information about you electronically using the <strong>Consent</strong> lawful base.
            </p>
            
            <h4 className="text-lg font-bold mb-3">What do we collect, why, and how do we process your data?</h4>
            <ol className="list-decimal pl-6 mb-6 space-y-4">
              <li>
                <strong>Username and password</strong><br />
                We store your username and password for authentication and for security purposes.<br />
                Also, in the case where you have a special request for us, we can identify you by your username.
                Your username is also logged with your sent bug reports.
              </li>
              <li>
                <strong>Group guests' nicknames</strong><br />
                Guest member nicknames are stored solely to facilitate group functionality and are not linked to a 
                registered account. This data is visible only to group members.
              </li>
              <li>
                <strong>Transactions' and shopping lists' data</strong><br />
                We store the data you provided (name, note, amount, parties, and reactions of transactions;
                shopping requests in shopping lists) to calculate
                your balances, to show these to the belonging members in the groups and to create a better user
                experience for you.
              </li>
              <li>
                <strong>Other user data</strong><br />
                We store your currencies, your nicknames, and your language set to make
                you customize Dodo as you wish.
                We store the group you last viewed to make your experience better.
              </li>
              <li>
                <strong>Payment methods</strong><br />
                Users can add payment methods to facilitate transactions with other users. You are not obliged 
                to add payment methods, but it can enhance your experience on our platform. Payment methods are 
                stored as text provided by the user and are not validated or verified by us. Users are responsible 
                for ensuring the accuracy and validity of the information entered.
              </li>
              <li>
                <strong>Receipt Scanning Feature</strong><br />
                Users can scan receipts using their device's camera to extract information such as item names, amounts, 
                and totals. You are not obliged to scan receipts in order to use the app, but it can enhance your experience.
                This feature relies on Google Gemini's processing capabilities. The receipt photos are 
                transmitted directly from the user's device to Google Gemini for processing and are not sent to or 
                stored on our servers.<br />
                The receipt data is processed solely to extract information for the user's convenience and is not shared 
                with other group members unless the user explicitly saves the extracted information within the app.
                Even in this case, only processed receipt data (such as the name of the store, total cost, or cost per user) 
                is stored on our servers, receipt items are not.
              </li>
              <li>
                <strong>Sign-Up and Login via Google or Apple OAuth</strong><br />
                Users can sign up or log in using their Google or Apple accounts via OAuth. 
                This feature does not request any permissions from the user's Google or Apple account. 
                However, Google and Apple provide the user's email address and a unique user ID for authentication purposes.
                <ul className="list-disc pl-6 mt-2 space-y-1">
                  <li>
                    The email address is not stored or used by Dodo.
                  </li>
                  <li>
                    The unique user ID is stored in a hashed format on our servers for added security. This ID is used solely for account creation and login authentication.
                  </li>
                </ul>
              </li>
              <li>
                <strong>Statistics</strong><br />
                We can create anonymous usage statistics (eg. average group size, what the favorite color
                themes are) to see which of our features are popular and to make Dodo better.
              </li>
            </ol>
            
            <h4 className="text-lg font-bold mb-3">Who can see my data?</h4>
            <p className="mb-6">
              Your username, nicknames, and the data mentioned in point 3. in the above list can be seen by other
              members whom you are in a group with. Any other data can only be accessed by you.<br />
              Note that your data is stored on our servers and, for technical reasons, can be accessed by the
              server administrators (us).
            </p>
            
            <p className="font-bold mb-6">
              We do not send / sell any of the mentioned data above to any third party.
            </p>
            
            <p className="mb-6">
              We keep your information for as long as you have a registered account with us.
              You can delete your account and all your personal data in the Dodo app's profile settings.
              Note that if you leave a group, your transactions will remain visible without your name/nickname
              attached to them.
            </p>
            
            <hr className="my-6" />
            
            <p className="mb-6">
              If, as determined by us, the lawful basis upon which we process your personal
              information changes, we will notify you about the change and any new lawful basis
              to be used if required. We shall stop processing your personal information if the
              lawful basis used is no longer relevant.
            </p>
            
            <h2 className="text-2xl font-bold mt-8 mb-4">Your individual rights</h2>
            <p className="mb-4">
              Under the GDPR, your rights are as follows: the right to be informed; the right of access; the right to
              rectification; the right to erasure; the right to restrict processing; the right to data portability;
              the right to object; and the right not to be subject to automated decision-making including profiling.
            </p>
            <p className="mb-4">
              <a href="https://ico.org.uk/for-organisations/guide-to-data-protection/guide-to-the-general-data-protection-regulation-gdpr/individual-rights/"
                target="_blank" rel="noopener noreferrer" className="text-dodo-blue hover:underline">
                You can read more about your rights in detail here.</a>
            </p>
            <p className="mb-4">
              Contact us (see our contact information below) if you want to claim your rights.
            </p>
            <p className="mb-4">
              You also have the right to complain to the
              <a href="https://www.naih.hu" target="_blank" rel="noopener noreferrer" className="text-dodo-blue hover:underline ml-1">NAIH</a> if you feel there is a
              problem with the way we are handling your data.
            </p>
            <p className="mb-6">
              We handle subject access requests in accordance with the GDPR.<br />
              Since our service does not include any decisions, our users are not subject to any automated
              decision-making algorithms. Also, we do not create profiles from our users.
            </p>
            
            <h2 className="text-2xl font-bold mt-8 mb-4">Internet cookies</h2>
            <p className="mb-6">
              We use cookies on this website to provide you with a better user experience and to make our site
              secure.
              We do this by placing a small text file on your device / computer hard drive.
              Our cookies are required to enjoy and use the full functionality of this website.
              We do not collect any personal information or usage data with cookies.
              We use a cookie control system that allows you to accept the use of cookies.
              Our cookies will be saved for a maximum of one week. Your web browser should provide you with the
              controls to manage and delete cookies from your device; please see your web browser options.
            </p>
            
            <h2 className="text-2xl font-bold mt-8 mb-4">Data security and protection</h2>
            <p className="mb-6">
              We ensure the security of any personal information we hold by using secure data
              storage technologies and precise procedures in how we store, access, and manage
              that information.<br />
              For accounts created using Google or Apple OAuth, the unique user ID provided during authentication 
              is stored in a hashed format to ensure it cannot directly identify users even in case of a data breach. 
              No additional personal data, such as email addresses, is stored by Dodo.<br />                
              Our methods meet the GDPR compliance requirement.
            </p>
            
            <h2 className="text-2xl font-bold mt-8 mb-4">Third parties</h2>
            <p className="mb-4">
              We are using some additional services hosted by third parties.
            </p>
            
            <h4 className="text-lg font-bold mb-3">In-App purchases</h4>
            <p className="mb-4">
              Our third parties (Google Play, App Store) may collect your account number and other financial details
              if you use our in-app purchases. We cannot access and we do not store this information as
              these transactions are handled by our third parties.<br />
              The above service's Privacy Policy Notices can be found here:
              <a href="https://policies.google.com/privacy" target="_blank" rel="noopener noreferrer" className="text-dodo-blue hover:underline ml-1">
                Google's privacy policy</a>,
              <a href="https://www.apple.com/uk/legal/privacy/en-ww/" target="_blank" rel="noopener noreferrer" className="text-dodo-blue hover:underline ml-1">
                Apple's privacy policy</a>.
            </p>
            
            <h4 className="text-lg font-bold mb-3">Notifications</h4>
            <p className="mb-4">
              We use Google Firebase's Cloud Messaging service for Android devices. For iOS devices, we use the Apple Push Notification service (APNs) to deliver notifications. The notifications are delivered through your Firebase Token, which are linked to your device.
              <br />The above service's Privacy Policy Notice can be applied, which <a href="https://firebase.google.com/support/privacy" target="_blank" rel="noopener noreferrer" className="text-dodo-blue hover:underline">can be found here</a>.
            </p>
            
            <h4 className="text-lg font-bold mb-3">Adverts & commissions</h4>
            <p className="mb-4">
              Our website and app may contain adverts. These are served through Google AdMob.
              We do not control the actual adverts seen / displayed.
              Our ad partners may collect data and use cookies for ad
              personalisation and measurement. Where ad preferences are requested as 'nonpersonalised' cookies
              may still be used for frequency capping, aggregated ad
              reporting and to combat fraud and abuse.<br />
              Clicking on any adverts may track your actions by using
              a cookie saved to your device. You can read more about cookies on this website
              above. Your actions are usually recorded as a referral from our website or app by this
              cookie. In most cases, we earn a very small commission from the advertiser or
              advertising partner, at no cost to you, whether you make a purchase on their
              website or not.
              We use advertising partners in these ways to help cover our
              expenses and generate an income which allows us
              to continue our work and provide you with the best
              overall experience and valued information.
              If you have any concerns about this, we suggest you do not click on any adverts found throughout
              our services.<br />
              <a href="https://policies.google.com/privacy?hl=en" target="_blank" rel="noopener noreferrer" className="text-dodo-blue hover:underline">Google's
                Privacy Policy Notice can be found here.</a>
            </p>
            
            <h4 className="text-lg font-bold mb-3">Receipt Processing</h4>
            <p className="mb-4">
              The receipt scanning feature uses Google Gemini's APIs through the intermediary of Firebase Vertex AI to process receipt images. 
              The interaction occurs directly between the user's device and Google Gemini's servers. Receipt images are not 
              stored by Dodo or transmitted through our servers.<br />
              Firebase Vertex AI's privacy policy can be found <a href="https://firebase.google.com/support/privacy" target="_blank" rel="noopener noreferrer" className="text-dodo-blue hover:underline">here</a>.<br />
              Google's privacy policy can be found <a href="https://policies.google.com/privacy?hl=en" target="_blank" rel="noopener noreferrer" className="text-dodo-blue hover:underline">here</a>.
            </p>
            
            <h4 className="text-lg font-bold mb-3">OAuth Authentication</h4>
            <p className="mb-6">
              Our app allows users to sign up and log in using Google or Apple accounts through OAuth. 
              These services provide us with a unique user ID and the user's email address to 
              facilitate account creation or authentication. We do not use the user's email address and store the
              user ID in a hashed format.<br />
              For more information about how these third parties handle your data, 
              you can refer to their respective privacy policies.<br />
              Apple's privacy policy can be found <a href="https://www.apple.com/legal/privacy/en-ww/" target="_blank" rel="noopener noreferrer" className="text-dodo-blue hover:underline">here</a>.<br />
              Google's privacy policy can be found <a href="https://policies.google.com/privacy?hl=en" target="_blank" rel="noopener noreferrer" className="text-dodo-blue hover:underline">here</a>.
            </p>
            
            <h2 className="text-2xl font-bold mt-8 mb-4">Resources & further information</h2>
            <ul className="list-disc pl-6 mb-6 space-y-1">
              <li>
                <a href="https://ico.org.uk/for-organisations/data-protection-reform/overview-of-the-gdpr/"
                  target="_blank" rel="noopener noreferrer" className="text-dodo-blue hover:underline">
                  Overview of the GDPR - General Data Protection Regulation
                </a>
              </li>
              <li>
                <a href="https://jamieking.co.uk/blog/privacy-policy-template-gdprprivacy-notice-template-example"
                  target="_blank" rel="noopener noreferrer" className="text-dodo-blue hover:underline">
                  Privacy Policy Template
                </a> v.4.1 Dec 2018 - Edited and customized by the business named above.
              </li>
            </ul>
            
            <h2 className="text-2xl font-bold mt-8 mb-4">Contact</h2>
            <p className="mb-1">
              Dominik Katkó, <a href="mailto:admin@dodoapp.net" target="_blank" rel="noopener noreferrer" className="text-dodo-blue hover:underline">admin@dodoapp.net</a>
            </p>
            <p className="mb-6">
              Sámuel Szajbély, <a href="mailto:developer@dodoapp.net" target="_blank" rel="noopener noreferrer" className="text-dodo-blue hover:underline">developer@dodoapp.net</a>
            </p>
          </div>
          
          <div className="text-center mt-6 text-gray-600">
            <p>Updated: 2025.01.09</p>
          </div>
          
          <div className="flex justify-center mt-8">
            <Link to="/" className="btn btn-primary">
              Back to Home
            </Link>
          </div>
        </div>
      </div>
    </div>
  );
};

export default PrivacyPolicy; 