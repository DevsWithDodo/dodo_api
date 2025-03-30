import React from "react";

const Hero = () => {
  return (
    <section className="py-16 md:py-24 bg-gradient-to-br from-white to-blue-50">
      <div className="container mx-auto px-4">
        <div className="flex flex-col lg:flex-row items-center">
          <div className="lg:w-1/2 lg:pr-10 mb-10 lg:mb-0">
            <h1 className="text-4xl md:text-5xl font-bold text-gray-900 mb-6">
              <span className="text-dodo-blue">Dodo</span> - Secure Bill Splitting
            </h1>
            <p className="text-xl text-gray-700 mb-8 leading-relaxed">
              Dodo is the perfect app for hassle-free vacationing with friends, sharing expenses with roommates, 
              or managing finances in a relationship. With its secure encryption, you can track your mutual expenses 
              with ease and peace of mind.
            </p>
            <p className="text-lg text-gray-700 mb-10">
              No more money disagreements - simply download Dodo and start tracking expenses.
              <span className="inline-block ml-2 text-2xl" role="img" aria-label="Dodo">🦤</span>
              The app will take care of the rest, ensuring the safety and privacy of your transactions.
            </p>
            <div className="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
              <a 
                href="#download" 
                className="btn btn-primary text-center"
              >
                Download Now
              </a>
              <a 
                href="#features" 
                className="btn btn-secondary text-center"
              >
                See Features
              </a>
            </div>
          </div>
          <div className="lg:w-1/2 flex justify-center">
            <div className="relative">
              {/* Main device image */}
              <img 
                src="/assets/screenshots/screenshot1.png" 
                alt="Dodo App" 
                className="w-72 md:w-80 h-auto rounded-3xl shadow-2xl z-10 relative" 
              />
              {/* Secondary device image, positioned behind and to the side */}
              <img 
                src="/assets/screenshots/screenshot2.png" 
                alt="Dodo App" 
                className="w-72 md:w-80 h-auto rounded-3xl shadow-xl absolute -right-10 md:-right-20 -bottom-10 md:-bottom-20 opacity-80" 
              />
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};

export default Hero; 