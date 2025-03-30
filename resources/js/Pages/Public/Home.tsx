import React from "react";
import Download from '../../Components/Download';
import Features from '../../Components/Features';
import Hero from '../../Components/Hero';
import Screenshots from '../../Components/Screenshots';

const HomePage = () => {
  return (
    <div className="overflow-x-hidden flex flex-col grow shrink-0">
      <Hero />
      <Features />
      <Screenshots />
      <Download />
    </div>
  );
};

export default HomePage; 