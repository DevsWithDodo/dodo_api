import React from 'react';
import DownloadButtons from './DownloadButtons';

const Download = () => {
  return (
    <section id="download" className="py-16 md:py-24 bg-gradient-to-br from-dodo-blue to-dodo-blue/80 text-white">
      <div className="container mx-auto px-4">
        <div className="text-center mb-12">
          <h2 className="text-3xl md:text-4xl font-bold mb-4">
            Download <span className="text-dodo-yellow">Dodo</span> Today
          </h2>
          <p className="text-xl max-w-2xl mx-auto opacity-90">
            Available on iOS, Android, Windows and the web. Start splitting bills securely with your friends.
          </p>
        </div>

        <DownloadButtons />
      </div>
    </section>
  );
};

export default Download; 