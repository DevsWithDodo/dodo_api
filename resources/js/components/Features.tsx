import React from "react";
import { cn } from "../lib/utils";

interface FeatureProps {
  icon: string;
  title: string;
  description: string;
  className?: string;
}

const Feature = ({ icon, title, description, className }: FeatureProps) => {
  return (
    <div className={cn("card p-6 transition-all duration-300 hover:shadow-lg", className)}>
      <div className="text-3xl mb-4" role="img" aria-label={title}>
        {icon}
      </div>
      <h3 className="text-xl font-semibold mb-3 text-gray-900">{title}</h3>
      <p className="text-gray-600">{description}</p>
    </div>
  );
};

const Features = () => {
  const featuresList = [
    {
      icon: "✅",
      title: "Simple Registration",
      description: "Tired of apps needing your phone number, email or banking data to work? We don't do that here. Easy log in just with a username and a pin.",
      emphasis: false,
    },
    {
      icon: "🔒",
      title: "Encryption",
      description: "Afraid that your transactions end up in the wrong hands? Dodo encrypts all your transactions, nicknames and balances with AES-256/128 encryption.",
      emphasis: false,
    },
    {
      icon: "🤩",
      title: "Easy-to-use UI",
      description: "You know those apps that look like they were made like 20 years ago? This is not one of those.",
      emphasis: true,
    },
    {
      icon: "🧐",
      title: "Complicated Expenses",
      description: "One of your buddies drank a few beers more than the other? You can track it with Dodo. Easy.",
      emphasis: false,
    },
    {
      icon: "📷",
      title: "Scan Receipts",
      description: "You went to the store and bought items for your friends? Just scan the receipt, and the AI✨will help you out.",
      emphasis: true,
    },
    {
      icon: "✉️",
      title: "Guests",
      description: "One of your friends is unwilling to use the app? 🙈 Don't worry, we've got you covered: just add them as a guest and track their expenses for them until they decide otherwise.",
      emphasis: false,
    },
    {
      icon: "🤑",
      title: "Currency Exchange",
      description: "You are on a vacation abroad and need to track an expense outside your usual currency? Done. It's that simple.",
      emphasis: true,
    },
    {
      icon: "🚌",
      title: "Categories",
      description: "You like your expenses sorted nicely? Just add a category to it.",
      emphasis: false,
    },
    {
      icon: "🛍️",
      title: "Shopping List",
      description: "Ran out of toilet paper? Write it on the list, one of your mates will surely pick it up. There is even a function to tell them you are in a store if they needed something.",
      emphasis: false,
    },
    {
      icon: "❓",
      title: "Settle Up",
      description: "Want to break even? Just hit the button and the app tells you the easiest way to settle up. You can even copy and paste de necessary payments to a group chat.",
      emphasis: true,
    },
    {
      icon: "📄",
      title: "Export to PDF and Excel",
      description: "You can download the group summary as a PDF or an XLS file. It's neat if you need it (or are a bit of a nerd and like to play around with data) 🤓",
      emphasis: false,
    },
    {
      icon: "🌈",
      title: "Color Themes",
      description: "It's just that. But they really are beautiful and there are plenty to choose from.",
      emphasis: true,
    },
    {
      icon: "📱",
      title: "Tablet and Foldable Mode",
      description: "If you have a bigger display, you should have more on it, right? We think so too. That's why the app adapts to the screen size you watch it on.",
      emphasis: false,
    },
    {
      icon: "🖥️",
      title: "Open Source",
      description: "It's like the honest way to tell you we don't steal your data.",
      emphasis: true,
    },
  ];

  return (
    <section id="features" className="py-16 md:py-24 bg-white">
      <div className="container mx-auto px-4">
        <div className="text-center mb-16">
          <h2 className="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
            Why Choose <span className="text-dodo-blue">Dodo</span>?
          </h2>
          <p className="text-xl text-gray-600 max-w-2xl mx-auto">
            Discover all the powerful features that make Dodo the best app for tracking and splitting expenses.
          </p>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          {featuresList.map((feature, index) => (
            <Feature
              key={index}
              icon={feature.icon}
              title={feature.title}
              description={feature.description}
              className={feature.emphasis ? "bg-gradient-to-br from-dodo-blue/10 to-white" : "bg-gray-50"}
            />
          ))}
        </div>

        <div className="mt-16 text-center">
          <p className="text-xl text-dodo-blue mb-6">❤️ Made with love: We are no big agency, no company. Just two buddies who code together.</p>
          <a href="#download" className="btn btn-primary">
            Get the App
          </a>
        </div>
      </div>
    </section>
  );
};

export default Features; 